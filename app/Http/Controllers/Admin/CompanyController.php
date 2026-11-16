<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function __construct(private DepositService $deposits)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        abort_unless(in_array($status, [null, '', 'pending', 'verified', 'rejected'], true), 404);

        $companies = Company::withCount('companyUsers')
            ->when($status, fn ($q, $value) => $q->where('verification_status', $value))
            ->orderByRaw("verification_status = 'pending' DESC")   // yang menunggu selalu di atas
            ->orderBy('company_name')
            ->get();

        $counts = Company::selectRaw('verification_status, COUNT(*) AS total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return view('admin.companies.index', compact('companies', 'counts', 'status'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255'],
            'corporate_code'      => ['nullable', 'string', 'max:20', 'alpha_dash', 'unique:companies,corporate_code'],
            'travel_policy_limit' => ['required', 'integer', 'min:0'],
            'initial_deposit'     => ['nullable', 'integer', 'min:0'],
            'finance_name'        => ['required', 'string', 'max:255'],
            'finance_email'       => ['required', 'email', 'unique:users,email'],
            'finance_password'    => ['required', 'string', 'min:8'],
        ]);

        $company = DB::transaction(function () use ($data, $request) {
            $company = Company::create([
                'company_name'        => $data['company_name'],
                'corporate_code'      => strtoupper($data['corporate_code'] ?? '') ?: strtoupper(Str::random(6)),
                'travel_policy_limit' => $data['travel_policy_limit'],
                'verification_status' => 'verified',
                'verified_at'         => now(),
                'verified_by'         => $request->user()->id,
            ]);

            $user = User::create([
                'email'     => $data['finance_email'],
                'password'  => $data['finance_password'],
                'user_type' => 'corporate',
                // Password dibuat oleh admin (sementara): Finance wajib menggantinya saat login pertama
                'must_change_password' => true,
            ]);

            $company->companyUsers()->create([
                'user_id'         => $user->id,
                'full_name'       => $data['finance_name'],
                'role_in_company' => 'admin_finance',
                'department'      => 'Finance',
            ]);

            if (! empty($data['initial_deposit'])) {
                $this->deposits->credit(
                    $company,
                    (int) $data['initial_deposit'],
                    'topup',
                    __('ui.initial_deposit_note'),
                    null,
                    $request->user()
                );
            }

            return $company;
        });

        return redirect()->route('admin.companies.show', $company)->with('success', __('ui.company_saved'));
    }

    public function show(Company $company)
    {
        $company->load('companyUsers.user', 'companyUsers.position', 'city', 'verifier');

        $transactions = $company->transactions()->with('creator')->latest()->limit(30)->get();

        return view('admin.companies.show', compact('company', 'transactions'));
    }

    /** Tambah saldo setelah transfer dari perusahaan dikonfirmasi (disimulasikan). */
    public function topup(Request $request, Company $company)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        $this->deposits->credit($company, (int) $data['amount'], 'topup', $data['note'] ?? null, null, $request->user());

        return redirect()->route('admin.companies.show', $company)->with('success', __('ui.topup_done'));
    }

        /** Setujui pendaftaran: tetapkan limit awal dan anggaran bulanan bawaan. */
    public function verify(Request $request, Company $company)
    {
        abort_unless($company->verification_status === 'pending', 404);

        $data = $request->validate([
            'travel_policy_limit'   => ['required', 'integer', 'min:0'],
            'default_monthly_budget' => ['nullable', 'integer', 'min:0'],
        ]);

        $company->update($data + [
            'verification_status' => 'verified',
            'verification_note'   => null,
            'verified_at'         => now(),
            'verified_by'         => $request->user()->id,
        ]);

        return back()->with('success', __('ui.company_verified'));
    }

    public function reject(Request $request, Company $company)
    {
        abort_unless($company->verification_status === 'pending', 404);

        $data = $request->validate(['verification_note' => ['required', 'string', 'max:500']]);

        $company->update([
            'verification_status' => 'rejected',
            'verification_note'   => $data['verification_note'],
            'verified_at'         => now(),
            'verified_by'         => $request->user()->id,
        ]);

        return back()->with('success', __('ui.company_rejected'));
    }

    /** Buka kembali pendaftaran yang ditolak (misalnya setelah perusahaan melengkapi data). */
    public function reopen(Company $company)
    {
        abort_unless($company->verification_status === 'rejected', 404);

        $company->update(['verification_status' => 'pending', 'verification_note' => null]);

        return back()->with('success', __('ui.company_reopened'));
    }

    /** Dokumen legalitas: tampil di browser (PDF atau gambar). */
    public function document(Company $company, string $type)
    {
        $path = match ($type) {
            'tax'     => $company->tax_document_path,
            'license' => $company->license_document_path,
            default   => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}