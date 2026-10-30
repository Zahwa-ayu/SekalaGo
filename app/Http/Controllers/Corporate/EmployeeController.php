<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    use CorporateHelpers;

    public function index(Request $request)
    {
        $company   = $this->company($request);
        $employees = $company->companyUsers()->with(['user', 'position'])->orderBy('full_name')->get();

        return view('corporate.employees.index', compact('company', 'employees'));
    }

    public function create(Request $request)
    {
        $company = $this->company($request);

        return view('corporate.employees.create', [
            'employee'  => new CompanyUser(),
            'positions' => $company->positions()->orderBy('name')->get(),
            'isSelf'    => false,
        ]);
    }

    public function store(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'full_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8'],
            'department'      => ['nullable', 'string', 'max:100'],
            'position_id'     => ['nullable', Rule::exists('company_positions', 'id')->where('company_id', $company->id)],
            'role_in_company' => ['required', Rule::in(['employee', 'admin_finance'])],
        ]);

        DB::transaction(function () use ($company, $data) {
            $user = User::create([
                'email'     => $data['email'],
                'password'  => $data['password'],
                'user_type' => 'corporate',
            ]);

            $company->companyUsers()->create([
                'user_id'         => $user->id,
                'full_name'       => $data['full_name'],
                'role_in_company' => $data['role_in_company'],
                'department'      => $data['department'] ?? null,
                'position_id'     => $data['position_id'] ?? null,
            ]);
        });

        return redirect()->route('corporate.employees.index')->with('success', __('ui.employee_saved'));
    }

    public function edit(Request $request, CompanyUser $employee)
    {
        $company = $this->company($request);
        abort_unless($employee->company_id === $company->id, 404);

        $employee->load('user');

        return view('corporate.employees.edit', [
            'employee'  => $employee,
            'positions' => $company->positions()->orderBy('name')->get(),
            'isSelf'    => $employee->user_id === $request->user()->id,
        ]);
    }

    public function update(Request $request, CompanyUser $employee)
    {
        $company = $this->company($request);
        abort_unless($employee->company_id === $company->id, 404);

        $isSelf = $employee->user_id === $request->user()->id;

        $data = $request->validate([
            'full_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'password'        => ['nullable', 'string', 'min:8'],
            'department'      => ['nullable', 'string', 'max:100'],
            'position_id'     => ['nullable', Rule::exists('company_positions', 'id')->where('company_id', $company->id)],
            'role_in_company' => ['required', Rule::in(['employee', 'admin_finance'])],
        ]);

        DB::transaction(function () use ($employee, $data, $isSelf, $request) {
            $employee->update([
                'full_name'       => $data['full_name'],
                'department'      => $data['department'] ?? null,
                'position_id'     => $data['position_id'] ?? null,
                // Finance tidak bisa mengubah peran atau menonaktifkan dirinya sendiri
                'role_in_company' => $isSelf ? $employee->role_in_company : $data['role_in_company'],
            ]);

            $userChanges = ['email' => $data['email']];

            if (! empty($data['password'])) {
                $userChanges['password'] = $data['password'];
            }

            if (! $isSelf) {
                $userChanges['is_active'] = $request->boolean('is_active');
            }

            $employee->user->update($userChanges);
        });

        return redirect()->route('corporate.employees.index')->with('success', __('ui.employee_saved'));
    }
}