@php
    $authUser = auth()->user();

    // Layout dasbor (sidebar): admin, mitra, dan Finance corporate. Selain itu: etalase (navbar).
    // Untuk mengeluarkan mitra dari dasbor, hapus 'partner.*' di bawah.
    // Halaman akun (ganti password) ikut sidebar untuk admin, mitra, dan Finance.
    $useDashboard = $authUser && (
        request()->routeIs('admin.*', 'partner.*')
        || (request()->routeIs('corporate.*') && $authUser->companyUser?->isFinance())
        || (request()->routeIs('account.*') && (
            in_array($authUser->user_type, ['admin', 'partner'], true)
            || $authUser->companyUser?->isFinance()
        ))
    );
@endphp
@extends($useDashboard ? 'layouts.dashboard' : 'layouts.storefront')