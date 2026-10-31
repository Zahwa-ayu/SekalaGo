<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminStaff;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const MANAGEABLE_TYPES = ['admin', 'customer'];
    private const LOCKED_TYPES = ['partner', 'corporate'];

    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['adminStaff', 'customer', 'partner', 'companyUser'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim($request->query('q'));
                $query->where(function ($w) use ($q) {
                    $w->where('email', 'like', "%{$q}%")
                      ->orWhereHas('adminStaff', fn ($r) => $r->where('full_name', 'like', "%{$q}%"))
                      ->orWhereHas('customer', fn ($r) => $r->where('full_name', 'like', "%{$q}%"))
                      ->orWhereHas('companyUser', fn ($r) => $r->where('full_name', 'like', "%{$q}%"))
                      ->orWhereHas('partner', fn ($r) => $r->where('company_name', 'like', "%{$q}%"));
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('user_type', $request->query('type')))
            ->orderBy('email')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users'       => $users,
            'allTypes'    => ['admin', 'customer', 'partner', 'corporate'],
            'manageable'  => self::MANAGEABLE_TYPES,
            'lockedTypes' => self::LOCKED_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'user_type'    => ['required', Rule::in(self::MANAGEABLE_TYPES)],
            'password'     => ['required', Password::min(8)],
            'is_active'    => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data, $request) {
            $user = (new User())->forceFill([
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'phone_number' => $data['phone_number'] ?? null,
                'user_type'    => $data['user_type'],
                'is_active'    => $request->boolean('is_active'),
            ]);
            $user->save();

            $this->saveProfile($user, $data['full_name']);
        });

        return redirect()->route('admin.users.index')->with('success', __('ui.um_added'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'user_type'    => ['nullable', Rule::in(self::MANAGEABLE_TYPES)],
            'password'     => ['nullable', Password::min(8)],
        ]);

        DB::transaction(function () use ($data, $user, $request) {
            $attributes = [
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
            ];

            // Role hanya bisa diubah antara admin <-> customer, dan bukan untuk akun sendiri.
            $roleLocked = $user->is($request->user()) || in_array($user->user_type, self::LOCKED_TYPES, true);
            $newType = (! $roleLocked && ! empty($data['user_type'])) ? $data['user_type'] : $user->user_type;
            $attributes['user_type'] = $newType;

            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $user->forceFill($attributes)->save();

            $this->saveProfile($user, $data['full_name']);
        });

        return redirect()->route('admin.users.index')->with('success', __('ui.um_updated'));
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', __('ui.um_cannot_deactivate_self'));
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('success', $user->is_active ? __('ui.um_activated') : __('ui.um_deactivated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', __('ui.um_cannot_delete_self'));
        }

        try {
            $user->delete();
        } catch (QueryException) {
            return back()->with('error', __('ui.um_delete_failed'));
        }

        return redirect()->route('admin.users.index')->with('success', __('ui.um_deleted'));
    }

    /** Simpan nama ke tabel profil sesuai role. */
    private function saveProfile(User $user, string $name): void
    {
        match ($user->user_type) {
            'admin' => AdminStaff::updateOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $name] + ($user->adminStaff ? [] : ['position' => 'Admin'])
            ),
            'customer'  => Customer::updateOrCreate(['user_id' => $user->id], ['full_name' => $name]),
            'partner'   => $user->partner?->update(['company_name' => $name]),
            'corporate' => $user->companyUser?->update(['full_name' => $name]),
        };
    }
}