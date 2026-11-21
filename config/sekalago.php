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
    'corporate_default_limit' => 2000000,
    // Jumlah minimal pengajuan top-up saldo perusahaan (IDR)
    'topup_minimum' => 100000,
    'per_page' => 15,
    // Tampilkan tautan reset password di layar (hanya untuk demo tanpa server email)
    'show_reset_link' => env('SHOW_RESET_LINK', false),

    // Denda pembatalan setelah batas gratis, sampai sehari sebelum check-in (persen dari total, 1 sampai 99)
    'late_cancel_fee_percent' => 25,

    // Pembayaran pelanggan: mock (simulasi, hanya untuk pengembangan) atau midtrans
    'payment_driver' => env('PAYMENT_DRIVER', 'mock'),

    'locales' => [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
        'ko' => 'Korea / 한국어',
        'ja' => 'Jepang / 日本語',
        'ms' => 'Melayu',
        'es' => 'Spanyol / Español',
    ],

        // Saran nama bank di Profil Usaha mitra
    'banks' => [
        'BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN',
        'OCBC', 'Maybank', 'Panin', 'Mega', 'Bank Jago', 'SeaBank', 'Jenius (BTPN)',
    ],

        // Perkiraan biaya Midtrans per metode: persen dari jumlah + biaya tetap (IDR).
    // Kunci mengikuti payment_type dari Midtrans. Metode yang tidak terdaftar memakai 'default'.
    'gateway_fees' => [
        'default'       => ['percent' => 0.0, 'fixed' => 4000],
        'bank_transfer' => ['percent' => 0.0, 'fixed' => 4000],   // virtual account BCA, BNI, BRI, Permata
        'echannel'      => ['percent' => 0.0, 'fixed' => 4000],   // Mandiri Bill
        'gopay'         => ['percent' => 2.0, 'fixed' => 0],
        'shopeepay'     => ['percent' => 2.0, 'fixed' => 0],
        'qris'          => ['percent' => 0.7, 'fixed' => 0],
        'credit_card'   => ['percent' => 2.9, 'fixed' => 2000],
        'cstore'        => ['percent' => 0.0, 'fixed' => 5000],   // Indomaret, Alfamart
    ],

    // PPN atas biaya gateway (persen). Isi 0 jika tarif di atas sudah termasuk PPN.
    'gateway_vat_percent' => 11,
    
];