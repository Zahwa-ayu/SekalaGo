@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.verify_partners') }}</h1>

            <a href="{{ route('admin.partners.create') }}"
               class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.partner_add') }}
            </a>
        </div>

        {{-- FILTER STATUS --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (['pending', 'verified', 'rejected'] as $s)
                <a href="{{ route('admin.partners.index', ['status' => $s]) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm flex items-center gap-2 transition
                          {{ $status === $s ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                    {{ __('ui.verification_' . $s) }}
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $status === $s ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ $counts->get($s, 0) }}</span>
                </a>
            @endforeach
            <a href="{{ route('admin.partners.index', ['status' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs sm:text-sm transition
                      {{ $status === 'all' ? 'bg-sagara-navy text-white font-bold shadow-md' : 'bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-100' }}">
                {{ __('ui.all') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.company_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.email') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.business_license_no') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.registered_at') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.mou_menu') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($partners as $partner)
                            @php
                                $badge = match ($partner->verification_status) {
                                    'verified' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                                $mou = $partner->latestAgreement;
                                $mouBadge = ! $mou ? 'bg-slate-100 text-slate-500'
                                    : ($mou->status === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700');
                            @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $partner->company_name }}
                                    @if ($partner->source === 'admin')
                                        <span class="ml-1 px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[10px] font-bold">{{ __('ui.partner_added_by_admin') }}</span>
                                    @endif
                                    @if ($partner->proposedPercent() !== null && ! $partner->hasSignedAgreement())
                                        <span class="ml-1 px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold">{{ __('ui.proposes_short', ['percent' => rtrim(rtrim(number_format($partner->proposedPercent(), 2, '.', ''), '0'), '.')]) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $partner->user->email }}</td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $partner->business_license_no }}</td>
                                <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $partner->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">{{ __('ui.verification_' . $partner->verification_status) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $mouBadge }}">
                                        {{ $mou ? __('ui.mou_status_' . $mou->status) : __('ui.mou_none') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{-- <a href="{{ route('admin.partners.show', $partner) }}"
                                       class="px-3 py-1.5 text-xs font-bold border border-sky-500 text-sky-600 hover:bg-sky-500 hover:text-white rounded-lg transition whitespace-nowrap">
                                        {{ __('ui.review') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                    </a> --}}
                                    <a href="{{ route('admin.partners.show', $partner) }}"
                                       class="px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-lg transition whitespace-nowrap">
                                        {{ __('ui.review') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-handshake text-3xl mb-2 block"></i>
                                    {{ __('ui.no_partners') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $partners->links() }}</div>
    </section>
@endsection