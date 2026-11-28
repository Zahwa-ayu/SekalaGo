<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Support\AdminAbilities;

class DashboardMenu
{
    /** Satu pintu ke semua pengaturan akun (informasi, edit, password, bahasa). */
    private const ACCOUNT_SETTINGS = ['fa-user-gear', 'ui.account_settings', 'account.settings', ['account.*']];

    /**
     * Menu utama sidebar. Tiga bentuk baris:
     *  - item   : [icon, label (kunci bahasa), route, pola route untuk status aktif]
     *  - grup   : ['icon' => ..., 'label' => ..., 'children' => [item, item, ...]]
     *  - judul  : ['section' => label (kunci bahasa)] -> judul kecil pemisah kelompok menu
     * Baris yang rutenya belum ada otomatis dilewati, grup tanpa isi dan judul tanpa
     * menu di bawahnya tidak ditampilkan.
     */
    public static function for(User $user): array
    {
        // Admin: menu hanya memuat halaman yang boleh dibuka sub-role-nya (aturan yang sama dengan pagar rute)
        $visible = $user->user_type === 'admin'
            ? fn (string $route) => AdminAbilities::allowsRoute($user, $route)
            : null;

        return self::filter(match ($user->user_type) {
            'admin' => [
                // Ringkasan (tanpa judul, selalu paling atas)
                ['fa-gauge', 'ui.dashboard', 'admin.dashboard', ['admin.dashboard']],
                ['fa-chart-line', 'ui.analytics', 'admin.analytics.index', ['admin.analytics.*']],

                // Pekerjaan harian admin
                ['section' => 'ui.menu_operations'],
                ['fa-handshake', 'ui.verify_partners', 'admin.partners.index', ['admin.partners.*']],
                [
                    'icon'     => 'fa-clipboard-check',
                    'label'    => 'ui.review_products',
                    'children' => [
                        ['fa-hotel', 'ui.review_hotels', 'admin.hotels.index', ['admin.hotels.*']],
                        ['fa-car-side', 'ui.review_vehicles', 'admin.vehicles.index', ['admin.vehicles.*']],
                    ],
                ],
                ['fa-receipt', 'ui.all_bookings', 'admin.bookings.index', ['admin.bookings.*']],
                ['fa-building', 'ui.corporate_management', 'admin.companies.index', ['admin.companies.*']],
                ['fa-credit-card', 'ui.payments', 'admin.payments.index', ['admin.payments.*']],
                ['fa-hand-holding-dollar', 'ui.topup_requests', 'admin.topups.index', ['admin.topups.*']],

                // Uang
                ['section' => 'ui.menu_finance'],
                ['fa-money-bill-transfer', 'ui.payouts', 'admin.payouts.index', ['admin.payouts.*']],

                // Data & isi situs
                ['section' => 'ui.menu_content'],
                ['fa-star', 'ui.reviews', 'admin.reviews.index', ['admin.reviews.*']],
                ['fa-life-ring', 'ui.issue_reports', 'admin.issues.index', ['admin.issues.*']],
                [
                    'icon'     => 'fa-database',
                    'label'    => 'ui.master_data',
                    'children' => [
                        ['fa-location-dot', 'ui.cities', 'admin.cities.index', ['admin.cities.*']],
                        ['fa-list-check', 'ui.facilities', 'admin.facilities.index', ['admin.facilities.*']],
                        ['fa-coins', 'ui.currencies', 'admin.currencies.index', ['admin.currencies.*']],
                        ['fa-building-columns', 'ui.bank_accounts', 'admin.bank-accounts.index', ['admin.bank-accounts.*']],
                    ],
                ],
                [
                    'icon'     => 'fa-newspaper',
                    'label'    => 'ui.cms',
                    'children' => [
                        ['fa-share-nodes', 'ui.social_media', 'admin.social-media.index', ['admin.social-media.*']],
                        ['fa-circle-question', 'ui.faq_management', 'admin.faqs.index', ['admin.faqs.*']],
                        ['fa-image', 'ui.banners', 'admin.banners.index', ['admin.banners.*']],
                        ['fa-file-lines', 'ui.static_pages', 'admin.pages.index', ['admin.pages.*']],
                    ],
                ],

                // Tim, pengguna admin, log, dan akun sendiri
                ['section' => 'ui.menu_system'],
                ['fa-people-group', 'ui.staff_team', 'admin.staff.index', ['admin.staff.*']],
                ['fa-users', 'ui.admin_users', 'admin.users.index', ['admin.users.*']],
                ['fa-clock-rotate-left', 'ui.activity_log', 'admin.activity.index', ['admin.activity.*']],
                self::ACCOUNT_SETTINGS,
            ],

            'partner' => self::partnerMenu($user),

            'corporate' => $user->companyUser?->isFinance()
                ? [
                    // Ringkasan (tanpa judul, selalu paling atas)
                    ['fa-gauge', 'ui.dashboard', 'corporate.dashboard', ['corporate.dashboard']],
                    ['fa-wallet', 'ui.topup_balance', 'corporate.topups.index', ['corporate.topups.*']],

                    // Karyawan dan aturan perusahaan
                    ['section' => 'ui.menu_company'],
                    ['fa-users', 'ui.employees', 'corporate.employees.index', ['corporate.employees.*']],
                    ['fa-sliders', 'ui.positions_limits', 'corporate.positions.index', ['corporate.positions.*', 'corporate.policy.*']],

                    // Pemesanan dan laporannya
                    ['section' => 'ui.menu_bookings'],
                    ['fa-clipboard-check', 'ui.company_bookings', 'corporate.bookings.index', ['corporate.bookings.*']],
                    ['fa-chart-column', 'ui.reports', 'corporate.reports.index', ['corporate.reports.*']],
                    ['fa-magnifying-glass-chart', 'ui.financial_audit', 'corporate.audit.index', ['corporate.audit.*']],

                    // Akun sendiri
                    ['section' => 'ui.menu_account'],
                    self::ACCOUNT_SETTINGS,
                ]
                : [],

            default => [],
        }, $visible);
    }

    /**
     * Pintasan di bawah menu utama (dipisah garis). Saat ini kosong untuk semua peran;
     * layout tetap memanggil method ini, jadi pintasan baru cukup ditambahkan di sini.
     * Contoh: 'corporate' => [['fa-magnifying-glass', 'ui.find_hotel', 'home', ['home', 'hotels.*']]],
     */
    public static function shortcuts(User $user): array
    {
        return self::filter(match ($user->user_type) {
            default => [],
        });
    }

    private static function partnerMenu(User $user): array
    {
        $partner = $user->partner;

        // Sebelum MOU ditandatangani: hanya MOU, profil, dan pengaturan akun
        if (! $partner?->hasSignedAgreement()) {
            return [
                ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                self::ACCOUNT_SETTINGS,
            ];
        }

        $hotels = $partner->offersHotels();
        $cars   = $partner->offersCars();

        // Judul kelompok yang tidak punya menu di bawahnya otomatis dibuang oleh filter()
        return array_values(array_filter([
            // Ringkasan (tanpa judul, selalu paling atas)
            ['fa-gauge', 'ui.dashboard', 'partner.dashboard', ['partner.dashboard']],

            // Pekerjaan harian mitra
            ['section' => 'ui.menu_bookings'],
            ['fa-receipt', 'ui.incoming_bookings', 'partner.bookings.index', ['partner.bookings.*']],
            $hotels ? ['fa-bell-concierge', 'ui.guests_page', 'partner.stays.index', ['partner.stays.*']] : null,
            $cars ? ['fa-car-on', 'ui.rentals', 'partner.rentals.index', ['partner.rentals.*']] : null,

            // Yang disewakan
            ['section' => 'ui.menu_products'],
            $hotels ? ['fa-hotel', 'ui.my_hotels', 'partner.hotels.index', ['partner.hotels.*', 'partner.room-types.*']] : null,
            $cars ? ['fa-car', 'ui.my_vehicles', 'partner.vehicles.index', ['partner.vehicles.*', 'partner.blackouts.*']] : null,

            // Uang
            ['section' => 'ui.menu_finance'],
            ['fa-wallet', 'ui.payouts', 'partner.payouts.index', ['partner.payouts.*']],
            ['fa-chart-column', 'ui.partner_reports', 'partner.reports.index', ['partner.reports.*']],

            // Masukan dan kendala
            ['section' => 'ui.menu_feedback'],
            ['fa-star', 'ui.reviews', 'partner.reviews.index', ['partner.reviews.*']],
            ['fa-life-ring', 'ui.issue_reports', 'partner.issues.index', ['partner.issues.*']],

            // Akun dan legalitas
            ['section' => 'ui.menu_account'],
            ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
            ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
            self::ACCOUNT_SETTINGS,
        ]));
    }

    /**
     * Ubah baris mentah menjadi struktur siap tampil. Item tanpa rute atau yang tidak boleh dibuka dibuang;
     * grup tanpa isi dibuang; grup dianggap aktif jika salah satu anaknya aktif.
     */
    private static function filter(array $rows, ?\Closure $visible = null): array
    {
        $items = collect($rows)
            ->map(function ($row) use ($visible) {
                // Judul kelompok
                if (isset($row['section'])) {
                    return [
                        'type'  => 'section',
                        'label' => $row['section'],
                    ];
                }

                if (isset($row['children'])) {
                    $children = self::filter($row['children'], $visible);

                    if ($children === []) {
                        return null;
                    }

                    return [
                        'type'     => 'group',
                        'icon'     => $row['icon'],
                        'label'    => $row['label'],
                        'children' => $children,
                        'active'   => collect($children)->pluck('active')->flatten()->all(),
                    ];
                }

                if (! Route::has($row[2]) || ($visible && ! $visible($row[2]))) {
                    return null;
                }

                return [
                    'type'   => 'item',
                    'icon'   => $row[0],
                    'label'  => $row[1],
                    'route'  => $row[2],
                    'active' => $row[3],
                ];
            })
            ->filter()
            ->values()
            ->all();

        return self::dropEmptySections($items);
    }

    /** Buang judul kelompok yang tidak diikuti menu apa pun (mis. semua rutenya belum ada atau tidak boleh dibuka). */
    private static function dropEmptySections(array $items): array
    {
        $result = [];

        foreach ($items as $i => $item) {
            if ($item['type'] === 'section') {
                $next = $items[$i + 1] ?? null;

                if ($next === null || $next['type'] === 'section') {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }
}