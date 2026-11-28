@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md';
        $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $trim  = fn ($rate) => $rate === null ? '-' : rtrim(rtrim(number_format($rate, 6, ',', '.'), '0'), ',');
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.currencies.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.currencies') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
            {{ $currency->code }} <span class="text-slate-400 font-semibold">{{ $currency->name }}</span>
        </h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM KIRI: FORM + RIWAYAT KURS --}}
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('admin.currencies.update', $currency) }}" class="{{ $card }} p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">{{ __('ui.currency_name') }}</label>
                            <input name="name" required maxlength="60" value="{{ old('name', $currency->name) }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ __('ui.currency_symbol') }}</label>
                            <input name="symbol" required maxlength="5" value="{{ old('symbol', $currency->symbol) }}" class="{{ $input }}">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="{{ $label }}">{{ __('ui.currency_decimals') }}</label>
                            <input type="number" name="decimal_places" min="0" max="4" required value="{{ old('decimal_places', $currency->decimal_places) }}" class="{{ $input }}">
                        </div>

                        @unless ($currency->isBase())
                            <label class="sm:col-span-2 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 pb-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $currency->is_active)) class="rounded border-slate-300 text-sagara-blue">
                                {{ __('ui.currency_selectable') }}
                            </label>
                        @endunless
                    </div>

                    <div class="flex flex-wrap gap-3 pt-5 border-t border-slate-100">
                        <button class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-check"></i> {{ __('ui.save_changes') }}
                        </button>
                        <a href="{{ route('admin.currencies.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.cancel') }}</a>
                    </div>
                </form>

                {{-- RIWAYAT KURS --}}
                <div class="{{ $card }} overflow-hidden">
                    <h2 class="px-6 pt-6 pb-3 font-extrabold text-slate-900">{{ __('ui.rate_history') }}</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="text-left px-6 py-2.5">{{ __('ui.payout_created_on') }}</th>
                                    <th class="text-right px-3 py-2.5">{{ __('ui.old_rate') }}</th>
                                    <th class="text-right px-3 py-2.5">{{ __('ui.new_rate') }}</th>
                                    <th class="text-left px-6 py-2.5">{{ __('ui.changed_by') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="px-6 py-2.5 text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-3 py-2.5 text-right text-slate-500">{{ $trim($log->old_rate) }}</td>
                                        <td class="px-3 py-2.5 text-right font-bold text-slate-900">{{ $trim($log->new_rate) }}</td>
                                        <td class="px-6 py-2.5 text-xs text-slate-500">{{ $log->user?->email ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs">{{ __('ui.rate_history_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: KETERANGAN --}}
            <div class="{{ $card }} p-6 space-y-3 text-xs text-slate-600 lg:sticky lg:top-24">
                <div class="font-extrabold text-slate-900 text-sm"><i class="fa-solid fa-circle-info text-sagara-blue"></i> {{ __('ui.currencies') }}</div>
                <p>{{ __('ui.currency_rate_note') }}</p>

                @if ($currency->isBase())
                    <p class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">{{ __('ui.currency_base_locked') }}</p>
                @endif
            </div>
        </div>
    </section>
@endsection