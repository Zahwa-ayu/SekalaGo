@extends('layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
        // Pengguna dengan sidebar memakai header lengket; yang memakai navbar butuh ruang di atas
        $withSidebar = in_array($authUser->user_type, ['admin', 'partner'], true) || $authUser->companyUser?->isFinance();
    @endphp

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 {{ $withSidebar ? 'py-8' : 'pt-28 pb-12' }} space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.account_settings') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.account_settings_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        @yield('account')
    </section>
@endsection