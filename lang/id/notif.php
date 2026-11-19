<?php

return [
    'greeting' => 'Halo,',
    'open' => 'Buka di SekalaGo',
    'footer' => 'Anda bisa mengatur notifikasi email di Pengaturan Akun.',
    'by_customer' => 'pelanggan', 'by_admin' => 'admin', 'by_partner' => 'mitra',
    'refund_line' => 'Dana :amount dikembalikan.',
    'type_hotel' => 'Hotel', 'type_vehicle' => 'Armada',
    'reason_limit' => 'melebihi batas jabatan', 'reason_budget' => 'melebihi anggaran bulanan', 'reason_limit_budget' => 'melebihi batas jabatan dan anggaran bulanan',

    // Pelanggan dan karyawan
    'booking_confirmed' => ['title' => 'Pesanan terkonfirmasi: :code', 'body' => ':name (:period) sudah terkonfirmasi. Total :amount.'],
    'booking_cancelled' => ['title' => 'Pesanan dibatalkan: :code', 'body' => 'Pesanan :name dibatalkan oleh :by. :refund'],
    'booking_expired' => ['title' => 'Pesanan kedaluwarsa: :code', 'body' => 'Pesanan :name tidak diproses karena batas waktunya lewat. Silakan pesan lagi jika masih ingin.'],
    'approval_approved' => ['title' => 'Pesanan disetujui: :code', 'body' => 'Finance menyetujui pesanan :name. Pesanan sudah terkonfirmasi.'],
    'approval_rejected' => ['title' => 'Pesanan ditolak: :code', 'body' => 'Finance menolak pesanan :name. Catatan: :note'],
    'late_fee_recorded' => ['title' => 'Denda keterlambatan dicatat: :code', 'body' => 'Mobil :name dikembalikan terlambat. Denda :amount dibayar langsung ke vendor.'],
    'no_show_recorded' => ['title' => 'Kedatangan tidak tercatat: :code', 'body' => 'Hotel mencatat Anda tidak hadir untuk :name. Merasa keliru? Hubungi hotel atau admin SekalaGo.'],

    // Mitra
    'new_booking' => ['title' => 'Pesanan baru: :code', 'body' => ':guest memesan :name (:period). Hak mitra :amount.'],
    'booking_cancelled_partner' => ['title' => 'Pesanan dibatalkan: :code', 'body' => 'Pesanan :name atas nama :guest dibatalkan (:by). Stok sudah dikembalikan.'],
    'extension_done' => ['title' => 'Sewa diperpanjang: :code', 'body' => 'Sewa :name diperpanjang sampai :date (tambahan :amount).'],
    'payout_created' => ['title' => 'Pencairan dibuat: :code', 'body' => 'Pencairan :amount ke :bank sedang diproses admin.'],
    'payout_paid' => ['title' => 'Dana ditransfer: :code', 'body' => 'Pencairan :amount sudah ditransfer. Bukti transfer ada di halaman Pencairan Dana.'],
    'product_approved' => ['title' => ':type disetujui', 'body' => ':name sudah disetujui dan tampil di SekalaGo.'],
    'product_rejected' => ['title' => ':type ditolak', 'body' => ':name ditolak. Alasan: :reason'],
    'partner_verified' => ['title' => 'Pendaftaran mitra disetujui', 'body' => 'Akun mitra Anda terverifikasi. Tinjau dan tandatangani MOU untuk mulai berjualan.'],
    'partner_rejected' => ['title' => 'Pendaftaran mitra ditolak', 'body' => 'Pendaftaran Anda ditolak. Alasan: :note'],

    // Finance perusahaan
    'approval_requested' => ['title' => 'Persetujuan perjalanan: :code', 'body' => ':employee mengajukan :name (:period), :amount. Alasan: :reason.'],
    'company_verified' => ['title' => 'Perusahaan disetujui', 'body' => ':company sudah disetujui. Silakan masuk untuk menambah karyawan dan mengisi saldo.'],
    'company_rejected' => ['title' => 'Pendaftaran perusahaan ditolak', 'body' => 'Pendaftaran :company ditolak. Alasan: :note'],

    // Admin
    'partner_registered' => ['title' => 'Mitra baru mendaftar', 'body' => ':company menunggu verifikasi.'],
    'mou_signed' => ['title' => 'MOU ditandatangani', 'body' => ':company menandatangani MOU dengan komisi :commission%.'],
    'company_registered' => ['title' => 'Perusahaan baru mendaftar', 'body' => ':company menunggu verifikasi.'],

    'refund_manual' => ['title' => 'Refund manual diperlukan: :code', 'body' => 'Refund :amount (metode :method) tidak bisa diproses otomatis. Transfer manual lalu catat di halaman Pembayaran.'],
    'payment_orphan' => ['title' => 'Pembayaran masuk untuk pesanan tidak aktif', 'body' => 'Order :order sebesar :amount sudah dibayar, tetapi pesanannya kedaluwarsa atau dibatalkan. Kembalikan dana ke pelanggan.'],
    'payment_mismatch' => ['title' => 'Jumlah pembayaran tidak cocok', 'body' => 'Order :order dibayar dengan jumlah berbeda dari tagihan. Periksa di dashboard Midtrans.'],

    'topup_requested' => ['title' => 'Permintaan top-up: :company', 'body' => ':company mengajukan top-up :amount. Periksa bukti transfer dan mutasi rekening bank, lalu setujui atau tolak.'],
    'topup_approved' => ['title' => 'Top-up disetujui: :code', 'body' => 'Saldo perusahaan bertambah :amount.'],
    'topup_rejected' => ['title' => 'Top-up ditolak: :code', 'body' => 'Permintaan top-up :amount ditolak. Alasan: :reason'],
];