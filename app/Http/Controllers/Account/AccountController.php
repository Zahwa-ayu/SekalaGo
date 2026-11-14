<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Halaman akun: info akun + ringkasan bahasa & mata uang.
     */
    public function show(Request $request)
    {
        $user = $this->loadedUser($request);

        return view('account.index', [
            'user'          => $user,
            'name'          => $this->displayName($user),
            'roleKey'       => $this->roleKey($user),
            'locales'       => $this->locales(),
            'currentLocale' => app()->getLocale(),
            'currencies'    => Currency::orderBy('code')->get(),
            'currentCode'   => app(CurrencyService::class)->current()->code,
        ]);
    }

    /**
     * Halaman ganti bahasa & mata uang.
     * Penyimpanan memakai PreferenceController yang sudah ada (preferences.update).
     */
    public function preferences(Request $request)
    {
        return view('account.preferences', [
            'locales'       => $this->locales(),
            'currentLocale' => app()->getLocale(),
            'currencies'    => Currency::active()->orderBy('code')->get(),
            'currentCode'   => app(CurrencyService::class)->current()->code,
        ]);
    }

    /** Halaman "Edit Akun" (profil, kontak, dan ganti password). */
    public function edit(Request $request)
    {
        $user = $this->loadedUser($request);

        return view('account.edit', [
            'user'    => $user,
            'profile' => $this->profile($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $type = $user->user_type;

        $rules = [
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ];

        if (in_array($type, ['customer', 'admin', 'corporate'], true)) {
            $rules['full_name'] = ['required', 'string', 'max:255'];
        }

        if ($type === 'customer') {
            $rules['birth_date'] = ['nullable', 'date', 'before:today'];
            $rules['gender']     = ['nullable', Rule::in(['male', 'female'])];
        }

        if ($type === 'admin') {
            $rules['position'] = ['nullable', 'string', 'max:100'];
        }

        // Mengganti email butuh password saat ini
        if (strcasecmp((string) $request->input('email'), $user->email) !== 0) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($user, $type, $data) {
            $user->update([
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
            ]);

            match ($type) {
                'customer'  => $user->customer()->updateOrCreate([], [
                    'full_name'  => $data['full_name'],
                    'birth_date' => $data['birth_date'] ?? null,
                    'gender'     => $data['gender'] ?? null,
                ]),
                'admin'     => $user->adminStaff()->updateOrCreate([], [
                    'full_name' => $data['full_name'],
                    'position'  => $data['position'] ?? null,
                ]),
                'corporate' => $user->companyUser?->update(['full_name' => $data['full_name']]),
                default     => null,   // mitra: data usaha dikelola di Profil Usaha
            };
        });

        return redirect()->route('account.settings')->with('success', __('ui.account_updated'));
    }

    // ---------------------------------------------------------------- helper

    private function loadedUser(Request $request)
    {
        return $request->user()->load('customer', 'partner', 'adminStaff', 'companyUser.company', 'companyUser.position');
    }

    /** Baris profil yang menyimpan nama, sesuai peran (mitra tidak punya: namanya nama usaha). */
    private function profile($user)
    {
        return match ($user->user_type) {
            'customer'  => $user->customer,
            'admin'     => $user->adminStaff,
            'corporate' => $user->companyUser,
            default     => null,
        };
    }

    private function displayName($user): string
    {
        return $user->customer?->full_name
            ?? $user->adminStaff?->full_name
            ?? $user->companyUser?->full_name
            ?? $user->partner?->company_name
            ?? $user->email;
    }

    private function roleKey($user): string
    {
        return $user->user_type === 'corporate' && $user->companyUser?->isFinance()
            ? 'finance'
            : $user->user_type;
    }

    /** @return array<string, string> kode bahasa => nama */
    private function locales(): array
    {
        return collect(config('sekalago.locales'))
            ->map(fn ($value, $code) => is_array($value) ? ($value['name'] ?? $value['label'] ?? strtoupper($code)) : $value)
            ->all();
    }
}