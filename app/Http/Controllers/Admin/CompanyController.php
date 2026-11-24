<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Http\Request;
use App\Http\Controllers\CorporateRegistrationController;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            ->search($request->query('q'), ['company_name', 'corporate_code', 'tax_id'])
            ->orderByRaw("verification_status = 'pending' DESC")   // yang menunggu selalu di atas
            ->orderBy('company_name')
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        $counts = Company::selectRaw('verification_status, COUNT(*) AS total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return view('admin.companies.index', compact('companies', 'counts', 'status'));
    }

    public function create()
    {
        return view('admin.companies.create', [
            'cities'     => City::optionsFor(),
            'legalForms' => CorporateRegistrationController::LEGAL_FORMS,
        ]);
    }

    public function store(Request $request)
    {
        // NPWP ditulis dengan titik dan strip: disimpan sebagai angka saja
        $request->merge(['tax_id' => preg_replace('/\D/', '', (string) $request->input('tax_id'))]);

        $phone = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];
        $file  = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'];

        $data = $request->validate([
            'company_name'           => ['required', 'string', 'max:255', Rule::unique('companies', 'company_name')],
            'legal_form'             => ['required', Rule::in(CorporateRegistrationController::LEGAL_FORMS)],
            'tax_id'                 => ['required', 'digits_between:15,16', Rule::unique('companies', 'tax_id')],
            'business_license_no'    => ['required', 'string', 'regex:/^[0-9A-Za-z.\-\/]{8,30}$/'],
            'address'                => ['required', 'string', 'max:255'],
            'city_id'                => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'phone'                  => $phone,
            'industry'               => ['nullable', 'string', 'max:60'],

            'tax_document'           => $file,
            'license_document'       => $file,

            'corporate_code'         => ['nullable', 'string', 'max:20', 'alpha_dash', 'unique:companies,corporate_code'],
            'travel_policy_limit'    => ['required', 'integer', 'min:0'],
            'default_monthly_budget' => ['nullable', 'integer', 'min:0'],
            'initial_deposit'        => ['nullable', 'integer', 'min:0'],
            'initial_deposit_reference' => ['nullable', 'string', 'max:100'],

            'finance_name'           => ['required', 'string', 'max:255'],
            'finance_phone'          => $phone,
            'finance_email'          => ['required', 'email', 'unique:users,email'],
            'finance_password'       => ['required', 'string', 'min:8'],
        ]);

        $paths = [];

        try {
            $company = DB::transaction(function () use ($data, $request, &$paths) {
                $company = Company::create([
                    'company_name'           => $data['company_name'],
                    'corporate_code'         => strtoupper($data['corporate_code'] ?? '') ?: strtoupper(Str::random(6)),
                    'legal_form'             => $data['legal_form'],
                    'tax_id'                 => $data['tax_id'],
                    'business_license_no'    => $data['business_license_no'],
                    'address'                => $data['address'],
                    'city_id'                => $data['city_id'],
                    'phone'                  => $data['phone'],
                    'industry'               => $data['industry'] ?? null,
                    'travel_policy_limit'    => $data['travel_policy_limit'],
                    'default_monthly_budget' => $data['default_monthly_budget'] ?? null,
                    'source'                 => 'admin',
                    'verification_status'    => 'verified',
                    'verified_at'            => now(),
                    'verified_by'            => $request->user()->id,
                ]);

                // Dokumen disimpan di disk privat; hanya admin yang bisa membukanya
                foreach (['tax_document' => 'tax_document_path', 'license_document' => 'license_document_path'] as $input => $column) {
                    if ($request->hasFile($input)) {
                        $paths[$input] = $request->file($input)->store("corporate-documents/{$company->id}", 'local');
                        $company->{$column} = $paths[$input];
                    }
                }

                $company->save();

                $user = User::create([
                    'email'                => $data['finance_email'],
                    'password'             => $data['finance_password'],
                    'phone_number'         => $data['finance_phone'],
                    'user_type'            => 'corporate',
                    'must_change_password' => true,   // password sementara dari admin wajib diganti
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
                        $data['initial_deposit_reference'] ?? null,
                        $request->user()
                    );
                }

                return $company;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        return redirect()->route('admin.companies.show', $company)->with('success', __('ui.company_saved'));
    }

    /** Melengkapi dokumen yang belum diunggah saat perusahaan dibuat. */
    public function uploadDocuments(Request $request, Company $company)
    {
        $request->validate([
            'tax_document'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
            'license_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ]);

        foreach (['tax_document' => 'tax_document_path', 'license_document' => 'license_document_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $old = $company->{$column};

                $company->update([$column => $request->file($input)->store("corporate-documents/{$company->id}", 'local')]);

                if ($old) {
                    Storage::disk('local')->delete($old);
                }
            }
        }

        return back()->with('success', __('ui.company_documents_saved'));
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
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $this->deposits->credit($company, (int) $data['amount'], 'topup', $data['note'] ?? null, $data['reference'] ?? null, $request->user());

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