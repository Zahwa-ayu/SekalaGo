<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class DashboardMenu
{
    /** Satu pintu ke semua pengaturan akun (informasi, edit, password, bahasa). */
    private const ACCOUNT_SETTINGS = ['fa-user-gear', 'ui.account_settings', 'account.settings', ['account.*']];

    /**
     * Menu utama sidebar. Dua bentuk baris:
     *  - item  : [icon, label (kunci bahasa), route, pola route untuk status aktif]
     *  - grup  : ['icon' => ..., 'label' => ..., 'children' => [item, item, ...]]
     * Baris yang rutenya belum ada otomatis dilewati, dan grup tanpa isi tidak ditampilkan.
     */
    public static function for(User $user): array
    {
        return self::filter(match ($user->user_type) {
            'admin' => [
                ['fa-gauge', 'ui.dashboard', 'admin.dashboard', ['admin.dashboard']],
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
                [
                    'icon'     => 'fa-database',
                    'label'    => 'ui.master_data',
                    'children' => [
                        ['fa-location-dot', 'ui.cities', 'admin.cities.index', ['admin.cities.*']],
                        // Tambahkan di sini begitu halamannya ada: Fasilitas, Mata Uang & Kurs
                    ],
                ],
                [
                    'icon'     => 'fa-newspaper',
                    'label'    => 'ui.cms',
                    'children' => [
                        ['fa-share-nodes', 'ui.social_media', 'admin.social-media.index', ['admin.social-media.*']],
                        ['fa-circle-question', 'ui.faq_management', 'admin.faqs.index', ['admin.faqs.*']],
                        ['fa-image', 'ui.banners', 'admin.banners.index', ['admin.banners.*']],   // baris baru
                    ],
                ],
                ['fa-users', 'ui.admin_users', 'admin.users.index', ['admin.users.*']],
                ['fa-clock-rotate-left', 'ui.activity_log', 'admin.activity.index', ['admin.activity.*']],
                self::ACCOUNT_SETTINGS,
                // Tambahkan di sini begitu halamannya ada, misalnya:
                // ['fa-chart-line', 'ui.analytics', 'admin.analytics.index', ['admin.analytics.*']],
                // ['fa-money-bill-transfer', 'ui.payouts', 'admin.payouts.index', ['admin.payouts.*']],
            ],

            'partner' => $user->partner?->hasSignedAgreement()
                ? [
                    ['fa-gauge', 'ui.dashboard', 'partner.dashboard', ['partner.dashboard']],
                    ['fa-hotel', 'ui.my_hotels', 'partner.hotels.index', ['partner.hotels.*', 'partner.room-types.*']],
                    ['fa-receipt', 'ui.incoming_bookings', 'partner.bookings.index', ['partner.bookings.*']],
                    ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                    ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                    ['fa-car', 'ui.my_vehicles', 'partner.vehicles.index', ['partner.vehicles.*', 'partner.blackouts.*']],
                    ['fa-car-on', 'ui.rentals', 'partner.rentals.index', ['partner.rentals.*']],
                    self::ACCOUNT_SETTINGS,
                ]
                : [
                    ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                    ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                    self::ACCOUNT_SETTINGS,
                ],

            'corporate' => $user->companyUser?->isFinance()
                ? [
                    ['fa-gauge', 'ui.dashboard', 'corporate.dashboard', ['corporate.dashboard']],
                    ['fa-users', 'ui.employees', 'corporate.employees.index', ['corporate.employees.*']],
                    ['fa-sliders', 'ui.positions_limits', 'corporate.positions.index', ['corporate.positions.*', 'corporate.policy.*']],
                    ['fa-clipboard-check', 'ui.company_bookings', 'corporate.bookings.index', ['corporate.bookings.*']],
                    ['fa-chart-column', 'ui.reports', 'corporate.reports.index', ['corporate.reports.*']],
                    self::ACCOUNT_SETTINGS,
                ]
                : [],

            default => [],
        });
    }

    /** Pintasan ke etalase (Finance tetap bisa mencari dan memesan hotel). */
    public static function shortcuts(User $user): array
    {
        return self::filter(match ($user->user_type) {
            'corporate' => [
                ['fa-magnifying-glass', 'ui.find_hotel', 'home', ['home', 'hotels.*']],
                ['fa-suitcase-rolling', 'ui.my_bookings', 'bookings.index', ['bookings.*']],
            ],
            default => [],
        });
    }

    /**
     * Ubah baris mentah menjadi struktur siap tampil. Item tanpa rute dibuang;
     * grup tanpa isi dibuang; grup dianggap aktif jika salah satu anaknya aktif.
     */
    private static function filter(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
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
    }
}