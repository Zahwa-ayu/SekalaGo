<?php

// Tipe kontak yang bisa dipilih admin. Tambah tipe baru cukup di sini
// (kalau perlu tautan khusus, tambahkan juga di ContactInfo::getHrefAttribute()).
return [
    'email' => [
        'name'        => 'Email',
        'icon'        => 'fa-solid fa-envelope',
        'placeholder' => 'cs@sekalago.com',
    ],
    'phone' => [
        'name'        => 'Telepon',
        'icon'        => 'fa-solid fa-phone',
        'placeholder' => '+62 274 123456',
    ],
    'whatsapp' => [
        'name'        => 'WhatsApp',
        'icon'        => 'fa-brands fa-whatsapp',
        'placeholder' => '0812 3456 7890',
    ],
    'address' => [
        'name'        => 'Alamat',
        'icon'        => 'fa-solid fa-location-dot',
        'placeholder' => 'Jl. Malioboro No. 1, Yogyakarta',
    ],
    'hours' => [
        'name'        => 'Jam Operasional',
        'icon'        => 'fa-solid fa-clock',
        'placeholder' => 'Senin - Jumat, 09.00 - 17.00 WIB',
    ],
];