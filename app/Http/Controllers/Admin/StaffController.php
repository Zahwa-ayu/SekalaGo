<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminAbilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', [
            'staff' => User::where('user_type', 'admin')->with('adminStaff')->orderBy('email')->get(),
            'roles' => AdminAbilities::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'position'     => ['nullable', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'sub_role'     => ['required', Rule::in(AdminAbilities::ROLES)],
            'password'     => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'email'                => $data['email'],
                'password'             => $data['password'],
                'phone_number'         => $data['phone_number'] ?? null,
                'user_type'            => 'admin',
                'must_change_password' => true,
            ]);

            $user->adminStaff()->create([
                'full_name' => $data['full_name'],
                'position'  => $data['position'] ?? null,
                'sub_role'  => $data['sub_role'],
            ]);
        });

        return back()->with('success', __('ui.staff_created'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->user_type === 'admin', 404);

        $data   = $request->validate(['sub_role' => ['required', Rule::in(AdminAbilities::ROLES)]]);
        $active = $request->boolean('is_active');

        if ($user->id === $request->user()->id) {
            return back()->with('error', __('ui.staff_cannot_self'));
        }

        $loosingSuper = AdminAbilities::roleOf($user) === AdminAbilities::SUPER
            && ($data['sub_role'] !== AdminAbilities::SUPER || ! $active);

        if ($loosingSuper && AdminAbilities::activeSupers($user->id) === 0) {
            return back()->with('error', __('ui.staff_last_super'));
        }

        DB::transaction(function () use ($user, $data, $active) {
            $staff = $user->adminStaff ?? $user->adminStaff()->create(['full_name' => $user->email]);
            $staff->update(['sub_role' => $data['sub_role']]);
            $user->update(['is_active' => $active]);
        });

        return back()->with('success', __('ui.staff_saved'));
    }
}