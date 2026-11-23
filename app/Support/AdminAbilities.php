<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class AdminAbilities
{
    public const SUPER   = 'super_admin';
    public const OPS     = 'admin_ops';
    public const FINANCE = 'admin_finance';
    public const ROLES   = [self::SUPER, self::OPS, self::FINANCE];

    /** Kemampuan tiap peran. '*' berarti semuanya. */
    private const GRANTS = [
        self::SUPER   => ['*'],
        self::OPS     => ['operations', 'bookings', 'companies', 'content'],
        self::FINANCE => ['analytics', 'bookings', 'companies', 'finance'],
    ];

    /**
     * Kemampuan yang dibutuhkan tiap rute admin. Pola yang cocok lebih dulu menang, jadi yang spesifik ditulis di atas.
     * 'any' = semua admin. Rute admin yang tidak terdaftar di sini hanya bisa dibuka super admin.
     */
    private const ROUTES = [
        'admin.dashboard'        => 'any',
        'admin.analytics.*'      => 'analytics',

        'admin.partners.*'       => 'operations',
        'admin.hotels.*'         => 'operations',
        'admin.vehicles.*'       => 'operations',
        'admin.bookings.cancel*' => 'operations',
        'admin.bookings.*'       => 'bookings',

        'admin.companies.audit*' => 'finance',
        'admin.companies.topup*' => 'finance',
        'admin.companies.index'  => 'companies',
        'admin.companies.show'   => 'companies',
        'admin.companies.*'      => 'operations',     // buat, verifikasi, tolak, buka kembali, dokumen

        'admin.payments.*'       => 'finance',
        'admin.topups.*'         => 'finance',
        'admin.payouts.*'        => 'finance',
        'admin.currencies.*'     => 'finance',
        'admin.bank-accounts.*'  => 'finance',

        'admin.cities.*'         => 'content',
        'admin.facilities.*'     => 'content',
        'admin.social-media.*'   => 'content',
        'admin.faqs.*'           => 'content',
        'admin.banners.*'        => 'content',

        'admin.staff.*'          => 'staff',
        'admin.users.*'          => 'staff',
        'admin.activity.*'       => 'system',
    ];

    /** Admin lama tanpa baris staf atau tanpa peran dianggap super admin supaya tidak terkunci. */
    public static function roleOf(User $user): string
    {
        return $user->adminStaff?->sub_role ?: self::SUPER;
    }

    public static function can(User $user, string $ability): bool
    {
        if ($user->user_type !== 'admin') {
            return false;
        }

        $grants = self::GRANTS[self::roleOf($user)] ?? [];

        return in_array('*', $grants, true) || in_array($ability, $grants, true);
    }

    public static function allowsRoute(User $user, string $routeName): bool
    {
        // Hanya rute admin yang diatur di sini
        if (! str_starts_with($routeName, 'admin.')) {
            return true;
        }

        $ability = null;

        foreach (self::ROUTES as $pattern => $needed) {
            if (Str::is($pattern, $routeName)) {
                $ability = $needed;
                break;
            }
        }

        return match (true) {
            $ability === 'any' => $user->user_type === 'admin',
            $ability === null  => self::roleOf($user) === self::SUPER,   // belum terdaftar: hanya super admin
            default            => self::can($user, $ability),
        };
    }

    /** Jumlah super admin aktif (selain satu pengguna), untuk menjaga agar tidak ada yang terkunci. */
    public static function activeSupers(?int $exceptUserId = null): int
    {
        return User::where('user_type', 'admin')
            ->where('is_active', true)
            ->when($exceptUserId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->with('adminStaff')
            ->get()
            ->filter(fn (User $user) => self::roleOf($user) === self::SUPER)
            ->count();
    }
}