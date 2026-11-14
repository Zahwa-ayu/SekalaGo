<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

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

                // Uang
                ['section' => 'ui.menu_finance'],
                ['fa-money-bill-transfer', 'ui.payouts', 'admin.payouts.index', ['admin.payouts.*']],

                // Data & isi situs
                ['section' => 'ui.menu_content'],
                [
                    'icon'     => 'fa-database',
                    'label'    => 'ui.master_data',
                    'children' => [
                        ['fa-location-dot', 'ui.cities', 'admin.cities.index', ['admin.cities.*']],
                        ['fa-coins', 'ui.currencies', 'admin.currencies.index', ['admin.currencies.*']],
                    ],
                ],
                [
                    'icon'     => 'fa-newspaper',
                    'label'    => 'ui.cms',
                    'children' => [
                        ['fa-share-nodes', 'ui.social_media', 'admin.social-media.index', ['admin.social-media.*']],
                        ['fa-circle-question', 'ui.faq_management', 'admin.faqs.index', ['admin.faqs.*']],
                        ['fa-image', 'ui.banners', 'admin.banners.index', ['admin.banners.*']],
                    ],
                ],

                // Pengguna admin, log, dan akun sendiri
                ['section' => 'ui.menu_system'],
                ['fa-users', 'ui.admin_users', 'admin.users.index', ['admin.users.*']],
                ['fa-clock-rotate-left', 'ui.activity_log', 'admin.activity.index', ['admin.activity.*']],
                self::ACCOUNT_SETTINGS,
            ],

            'partner' => $user->partner?->hasSignedAgreement()
                ? [
                    // Ringkasan (tanpa judul, selalu paling atas)
                    ['fa-gauge', 'ui.dashboard', 'partner.dashboard', ['partner.dashboard']],

                    // Bisnis hotel
                    ['section' => 'ui.menu_hotel'],
                    ['fa-hotel', 'ui.my_hotels', 'partner.hotels.index', ['partner.hotels.*', 'partner.room-types.*']],
                    ['fa-receipt', 'ui.incoming_bookings', 'partner.bookings.index', ['partner.bookings.*']],

                    // Bisnis rental mobil
                    ['section' => 'ui.menu_rental'],
                    ['fa-car', 'ui.my_vehicles', 'partner.vehicles.index', ['partner.vehicles.*', 'partner.blackouts.*']],
                    ['fa-car-on', 'ui.rentals', 'partner.rentals.index', ['partner.rentals.*']],

                    // Uang
                    ['section' => 'ui.menu_finance'],
                    ['fa-wallet', 'ui.payouts', 'partner.payouts.index', ['partner.payouts.*']],

                    // Dokumen dan data mitra
                    ['section' => 'ui.menu_account'],
                    ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                    ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                    self::ACCOUNT_SETTINGS,
                ]
                : [
                    ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                    ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                    self::ACCOUNT_SETTINGS,
                ],

            'corporate' => $user->companyUser?->isFinance()
                ? [
                    // Ringkasan (tanpa judul, selalu paling atas)
                    ['fa-gauge', 'ui.dashboard', 'corporate.dashboard', ['corporate.dashboard']],

                    // Karyawan dan aturan perusahaan
                    ['section' => 'ui.menu_company'],
                    ['fa-users', 'ui.employees', 'corporate.employees.index', ['corporate.employees.*']],
                    ['fa-sliders', 'ui.positions_limits', 'corporate.positions.index', ['corporate.positions.*', 'corporate.policy.*']],

                    // Pemesanan dan laporannya
                    ['section' => 'ui.menu_bookings'],
                    ['fa-clipboard-check', 'ui.company_bookings', 'corporate.bookings.index', ['corporate.bookings.*']],
                    ['fa-chart-column', 'ui.reports', 'corporate.reports.index', ['corporate.reports.*']],

                    // Akun sendiri
                    ['section' => 'ui.menu_account'],
                    self::ACCOUNT_SETTINGS,
                ]
                : [],

            default => [],
        });
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

    /**
     * Ubah baris mentah menjadi struktur siap tampil. Item tanpa rute dibuang;
     * grup tanpa isi dibuang; grup dianggap aktif jika salah satu anaknya aktif;
     * judul kelompok (section) dibuang jika tidak ada menu di bawahnya.
     */
    private static function filter(array $rows): array
    {
        $items = collect($rows)
            ->map(function ($row) {
                if (isset($row['section'])) {
                    return [
                        'type'  => 'section',
                        'label' => $row['section'],
                    ];
                }

                if (isset($row['children'])) {
                    $children = self::filter($row['children']);

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

                if (! Route::has($row[2])) {
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

    /** Buang judul kelompok yang tidak diikuti menu apa pun (mis. semua rutenya belum ada). */
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