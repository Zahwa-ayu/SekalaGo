<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    use CorporateHelpers;

    public function index(Request $request)
    {
        $company     = $this->company($request);
        $showRemoved = $request->boolean('removed');

        $employees = $company->companyUsers()
            ->with(['user', 'position'])
            ->when($showRemoved, fn ($q) => $q->whereNotNull('removed_at'), fn ($q) => $q->whereNull('removed_at'))
            ->orderBy('full_name')
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('corporate.employees.index', compact('company', 'employees', 'showRemoved'));
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
                // Password dibuat oleh Finance (sementara): karyawan wajib menggantinya saat login pertama
                'must_change_password' => true,
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

                // Reset password karyawan oleh Finance = password sementara.
                // Kalau Finance mengganti password miliknya sendiri, tidak perlu dipaksa ganti lagi.
                if (! $isSelf) {
                    $userChanges['must_change_password'] = true;
                }
            }

            if (! $isSelf) {
                $userChanges['is_active'] = $request->boolean('is_active');
            }

            $employee->user->update($userChanges);
        });

        return redirect()->route('corporate.employees.index')->with('success', __('ui.employee_saved'));
    }

        /** "Hapus" = dikeluarkan dari daftar dan akun dinonaktifkan. Riwayat pesanan dan saldo tetap utuh untuk audit. */
    public function destroy(Request $request, CompanyUser $employee)
    {
        $company = $this->company($request);
        abort_unless($employee->company_id === $company->id && ! $employee->isRemoved(), 404);

        if ($employee->user_id === $request->user()->id) {
            return back()->with('error', __('ui.employee_remove_self'));
        }

        $otherFinance = $company->companyUsers()
            ->whereNull('removed_at')
            ->where('role_in_company', 'admin_finance')
            ->where('id', '!=', $employee->id)
            ->exists();

        if ($employee->isFinance() && ! $otherFinance) {
            return back()->with('error', __('ui.employee_remove_last_finance'));
        }

        $hasPending = Booking::where('company_id', $company->id)
            ->where('user_id', $employee->user_id)
            ->where('status', 'pending_approval')
            ->exists();

        if ($hasPending) {
            return back()->with('error', __('ui.employee_remove_pending'));
        }

        DB::transaction(function () use ($employee, $request) {
            $employee->update(['removed_at' => now(), 'removed_by' => $request->user()->id]);
            $employee->user->update(['is_active' => false]);
        });

        return back()->with('success', __('ui.employee_removed'));
    }

    public function restore(Request $request, CompanyUser $employee)
    {
        $company = $this->company($request);
        abort_unless($employee->company_id === $company->id && $employee->isRemoved(), 404);

        DB::transaction(function () use ($employee) {
            $employee->update(['removed_at' => null, 'removed_by' => null]);
            $employee->user->update(['is_active' => true]);
        });

        return back()->with('success', __('ui.employee_restored'));
    }
}