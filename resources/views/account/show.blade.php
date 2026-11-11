@extends('account.layout')

@section('account')
    @php
        $member     = $user->companyUser;
        $localeName = $locales[app()->getLocale()] ?? strtoupper(app()->getLocale());

        $rows = [
            [__('ui.email'), $user->email],
            [__('ui.phone'), $user->phone_number ?: '-'],
            [__('ui.account_status'), $user->is_active ? __('ui.account_active') : __('ui.account_inactive')],
            [__('ui.member_since'), $user->created_at->format('d/m/Y')],
        ];
    @endphp

    {{-- KARTU PROFIL --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 flex items-center gap-5">
        <div class="w-16 h-16 rounded-full bg-sky-500 text-white text-2xl font-black flex items-center justify-center shadow-md shadow-sky-500/20 shrink-0">
            {{ strtoupper(mb_substr($name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <div class="text-xl font-extrabold text-slate-900 truncate">{{ $name }}</div>
            <div class="text-sm text-slate-500 truncate">{{ $user->email }}</div>
            <span class="inline-block mt-2 px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 text-[11px] font-bold">
                {{ __('ui.role_' . $roleKey) }}
            </span>
        </div>
        <a href="{{ route('account.edit') }}"
           class="ml-auto hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition">
            <i class="fa-solid fa-user-pen"></i> {{ __('ui.account_edit') }}
        </a>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- AKUN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-3">
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.account_contact') }}</h2>
            @foreach ($rows as [$label, $value])
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ $label }}</span>
                    <span class="font-semibold text-slate-900 text-right break-all">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        {{-- ORGANISASI / PROFIL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-3">
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.account_organization') }}</h2>

            @if ($user->user_type === 'partner' && $user->partner)
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.business_name') }}</span>
                    <span class="font-semibold text-slate-900 text-right">{{ $user->partner->company_name }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.account_status') }}</span>
                    <span class="font-semibold text-slate-900">{{ __('ui.account_verification_' . $user->partner->verification_status) }}</span>
                </div>
                <a href="{{ route('partner.profile.edit') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sky-600 hover:underline">
                    <i class="fa-solid fa-id-card"></i> {{ __('ui.business_profile') }}
                </a>

            @elseif ($member)
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.company') }}</span>
                    <span class="font-semibold text-slate-900 text-right">{{ $member->company?->company_name }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.department') }}</span>
                    <span class="font-semibold text-slate-900">{{ $member->department ?: '-' }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.position') }}</span>
                    <span class="font-semibold text-slate-900">{{ $member->position?->name ?: '-' }}</span>
                </div>

            @elseif ($user->user_type === 'admin')
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.position') }}</span>
                    <span class="font-semibold text-slate-900">{{ $user->adminStaff?->position ?: '-' }}</span>
                </div>

            @elseif ($user->customer)
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.birth_date') }}</span>
                    <span class="font-semibold text-slate-900">{{ $user->customer->birth_date ? \Illuminate\Support\Carbon::parse($user->customer->birth_date)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ __('ui.gender') }}</span>
                    <span class="font-semibold text-slate-900">{{ $user->customer->gender ? __('ui.gender_' . $user->customer->gender) : '-' }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- PREFERENSI --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1 text-sm">
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.account_preferences') }}</h2>
            <div class="text-slate-600">
                {{ __('ui.language') }}: <strong class="text-slate-900">{{ $localeName }}</strong>
                &middot; {{ __('ui.currency') }}: <strong class="text-slate-900">{{ $currency->code }}</strong>
            </div>
        </div>
        <a href="{{ route('account.preferences') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition">
            <i class="fa-solid fa-language mr-1"></i> {{ __('ui.language_currency') }}
        </a>
    </div>
@endsection