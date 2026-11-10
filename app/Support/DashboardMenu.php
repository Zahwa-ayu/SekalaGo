<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class DashboardMenu
{
    /** Item "Ganti password": dipakai admin, mitra, dan Finance. */
    private const CHANGE_PASSWORD = ['fa-key', 'ui.change_password', 'account.password.edit', ['account.password.*']];

    /** Menu utama sidebar. Tiap item: icon, label (kunci bahasa), route, dan pola route untuk status aktif. */
    public static function for(User $user): array
    {
        return self::filter(match ($user->user_type) {
            'admin' => [
                ['fa-gauge', 'ui.dashboard', 'admin.dashboard', ['admin.dashboard']],
                ['fa-handshake', 'ui.verify_partners', 'admin.partners.index', ['admin.partners.*']],
                ['fa-hotel', 'ui.review_hotels', 'admin.hotels.index', ['admin.hotels.*']],
                ['fa-receipt', 'ui.all_bookings', 'admin.bookings.index', ['admin.bookings.*']],
                ['fa-building', 'ui.companies', 'admin.companies.index', ['admin.companies.*']],
                ['fa-share-nodes', 'ui.social_media', 'admin.social-media.index', ['admin.social-media.*']],
                ['fa-circle-question', 'ui.faq_management', 'admin.faqs.index', ['admin.faqs.*']],
                ['fa-users', 'ui.users', 'admin.users.index', ['admin.users.*']],
                ['fa-clock-rotate-left', 'ui.activity_log', 'admin.activity.index', ['admin.activity.*']],
                ['fa-car-side', 'ui.review_vehicles', 'admin.vehicles.index', ['admin.vehicles.*']],
                self::CHANGE_PASSWORD,
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
                    self::CHANGE_PASSWORD,
                ]
                : [
                    ['fa-file-signature', 'ui.mou_menu', 'partner.agreement.show', ['partner.agreement.*']],
                    ['fa-id-card', 'ui.partner_profile', 'partner.profile.edit', ['partner.profile.*']],
                    self::CHANGE_PASSWORD,
                ],

            'corporate' => $user->companyUser?->isFinance()
                ? [
                    ['fa-gauge', 'ui.dashboard', 'corporate.dashboard', ['corporate.dashboard']],
                    ['fa-users', 'ui.employees', 'corporate.employees.index', ['corporate.employees.*']],
                    ['fa-sliders', 'ui.positions_limits', 'corporate.positions.index', ['corporate.positions.*', 'corporate.policy.*']],
                    ['fa-clipboard-check', 'ui.company_bookings', 'corporate.bookings.index', ['corporate.bookings.*']],
                    ['fa-chart-column', 'ui.reports', 'corporate.reports.index', ['corporate.reports.*']],
                    self::CHANGE_PASSWORD,
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

    private static function filter(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row) => Route::has($row[2]))
            ->map(fn ($row) => [
                'icon'   => $row[0],
                'label'  => $row[1],
                'route'  => $row[2],
                'active' => $row[3],
            ])
            ->values()
            ->all();
    }
}