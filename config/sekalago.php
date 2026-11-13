<?php

return [
    'default_locale'   => 'id',
    'default_currency' => 'IDR',
    'commission_rate'      => 0.15,
    'booking_hold_minutes' => 30,
        // Pencairan dana mitra
    'payout_hold_days'         => 1,        // hari tunggu setelah layanan selesai
    'payout_minimum'           => 100000,   // di bawah ini dibawa ke pencairan berikutnya (IDR)
    'payout_unmarked_car_days' => 7,        // mobil yang tidak pernah ditandai kembali dicairkan setelah sekian hari
    'approval_hold_hours' => 24,
    // Tampilkan tautan reset password di layar (hanya untuk demo tanpa server email)
    'show_reset_link' => env('SHOW_RESET_LINK', false),

    'locales' => [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
        'ko' => 'Korea / 한국어',
        'ja' => 'Jepang / 日本語',
        'ms' => 'Melayu',
        'es' => 'Spanyol / Español',
    ],
];