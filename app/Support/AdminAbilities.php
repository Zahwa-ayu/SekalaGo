<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class AdminAbilities
{
    public const SUPER   = 'super_admin';
    public const OPS     = 'admin_ops';
    public const FINANCE = 'admin_finance';
    public const MANAGER = 'manager';
    public const ROLES   = [self::SUPER, self::OPS, self::FINANCE, self::MANAGER];

    /** Hak mengubah data per peran. '*' berarti semuanya. */
    private const GRANTS = [
        self::SUPER   => ['*'],
        self::OPS     => ['operations', 'bookings', 'companies', 'content'],
        self::FINANCE => ['analytics', 'bookings', 'companies', 'finance'],
    ];

    /** Peran hanya lihat: halaman dengan kemampuan ini boleh dibuka (GET), tanpa pernah mengubah data. */
    private const VIEW_ONLY = [
        self::MANAGER => ['analytics', 'operations', 'bookings', 'companies', 'finance', 'system'],
    ];

    /** Halaman yang tidak dibuka untuk peran hanya lihat walau kemampuannya cocok: Master Data dan CMS. */
    private const VIEW_ONLY_HIDDEN = [
        'admin.cities.*', 'admin.facilities.*', 'admin.currencies.*', 'admin.bank-accounts.*',
        'admin.social-media.*', 'admin.faqs.*', 'admin.banners.*', 'admin.pages.*',
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
        'admin.reviews.*'        => 'operations',
        'admin.issues.*'         => 'operations',
        'admin.bookings.cancel*' => 'operations',
        'admin.bookings.*'       => 'bookings',

        'admin.companies.audit*' => 'finance',
        'admin.companies.topup*' => 'finance',
        'admin.companies.index'  => 'companies',
        'admin.companies.show'   => 'companies',
        'admin.companies.*'      => 'operations',

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
        'admin.pages.*'          => 'content',

        'admin.staff.*'          => 'staff',
        'admin.users.*'          => 'staff',
        'admin.activity.*'       => 'system',
    ];

    /** Admin lama tanpa baris staf atau tanpa peran dianggap super admin supaya tidak terkunci. */
    public static function roleOf(User $user): string
    {
        return $user->adminStaff?->sub_role ?: self::SUPER;
    }

    public static function isViewOnly(User $user): bool
    {
        return $user->user_type === 'admin' && isset(self::VIEW_ONLY[self::roleOf($user)]);
    }

    /** Hak mengubah data. Peran hanya lihat tidak pernah punya. */
    public static function can(User $user, string $ability): bool
    {
        if ($user->user_type !== 'admin') {
            return false;
        }

        $grants = self::GRANTS[self::roleOf($user)] ?? [];

        return in_array('*', $grants, true) || in_array($ability, $grants, true);
    }

    public static function allowsRoute(User $user, string $routeName, string $method = 'GET'): bool
    {
        // Hanya rute admin yang diatur di sini
        if (! str_starts_with($routeName, 'admin.')) {
            return true;
        }

        if ($user->user_type !== 'admin') {
            return false;
        }

        $ability = null;

        foreach (self::ROUTES as $pattern => $needed) {
            if (Str::is($pattern, $routeName)) {
                $ability = $needed;
                break;
            }
        }

        $role = self::roleOf($user);

        // Peran hanya lihat: hanya membaca (GET atau HEAD), tidak pernah mengubah data, dan tidak membuka Master Data dan CMS
        if (isset(self::VIEW_ONLY[$role])) {
            if (! in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
                return false;
            }

            foreach (self::VIEW_ONLY_HIDDEN as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return false;
                }
            }

            return $ability === 'any' || ($ability !== null && in_array($ability, self::VIEW_ONLY[$role], true));
        }

        return match (true) {
            $ability === 'any' => true,
            $ability === null  => $role === self::SUPER,   // belum terdaftar: hanya super admin
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