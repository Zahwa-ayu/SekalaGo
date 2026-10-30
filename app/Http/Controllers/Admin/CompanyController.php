<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function __construct(private DepositService $deposits)
    {
    }

    public function index()
    {
        $companies = Company::withCount('companyUsers')->orderBy('company_name')->get();

        return view('admin.companies.index', compact('companies'));
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
            ]);

            $user = User::create([
                'email'     => $data['finance_email'],
                'password'  => $data['finance_password'],
                'user_type' => 'corporate',
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
        $company->load('companyUsers.user', 'companyUsers.position');

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
}