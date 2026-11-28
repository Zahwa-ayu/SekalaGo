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

    /**
     * Peran hanya lihat: daftar halaman yang boleh dibuka (hanya GET, tidak pernah mengubah data).
     * Mau menambah atau mengurangi akses Manager? Ubah daftar ini saja. Contoh yang bisa ditambahkan:
     *   'admin.payments.*', 'admin.payouts.*', 'admin.topups.*'   (keuangan)
     *   'admin.activity.*'                                         (log aktivitas)
     *   'admin.partners.*', 'admin.hotels.*', 'admin.vehicles.*'   (mitra dan produk)
     */
    private const VIEW_ONLY_PAGES = [
        self::MANAGER => [
            'admin.dashboard',
            'admin.analytics.*',
            'admin.bookings.*',
            'admin.companies.index',
            'admin.companies.show',
            'admin.reviews.*',
            'admin.issues.*',
        ],
    ];

    /**
     * Kemampuan yang dibutuhkan tiap rute admin (untuk peran yang bisa mengubah data).
     * Pola yang cocok lebih dulu menang. 'any' = semua admin. Rute yang tidak terdaftar hanya untuk super admin.
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
        return $user->user_type === 'admin' && isset(self::VIEW_ONLY_PAGES[self::roleOf($user)]);
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

        $role = self::roleOf($user);

        // Peran hanya lihat: hanya membaca, dan hanya halaman yang ada di daftarnya
        if (isset(self::VIEW_ONLY_PAGES[$role])) {
            return in_array(strtoupper($method), ['GET', 'HEAD'], true)
                && Str::is(self::VIEW_ONLY_PAGES[$role], $routeName);
        }

        $ability = null;

        foreach (self::ROUTES as $pattern => $needed) {
            if (Str::is($pattern, $routeName)) {
                $ability = $needed;
                break;
            }
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