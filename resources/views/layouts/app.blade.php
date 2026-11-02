@php
    $authUser = auth()->user();

    // Layout dasbor (sidebar): admin, mitra, dan Finance corporate. Selain itu: etalase (navbar).
    // Untuk mengeluarkan mitra dari dasbor, hapus 'partner.*' di bawah.
    $useDashboard = $authUser && (
        request()->routeIs('admin.*', 'partner.*')
        || (request()->routeIs('corporate.*') && $authUser->companyUser?->isFinance())
    );
@endphp
@extends($useDashboard ? 'layouts.dashboard' : 'layouts.storefront')