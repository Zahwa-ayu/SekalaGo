@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $trim  = fn ($rate) => rtrim(rtrim(number_format($rate, 6, '.', ''), '0'), '.');
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.currencies') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.currencies_hint') }}</p>
        </div>

        <div class="rounded-2xl bg-sky-50 border border-sky-100 px-5 py-4 text-xs text-sky-900">
            <i class="fa-solid fa-circle-info"></i> {{ __('ui.currency_rate_note') }}
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- TAMBAH --}}
        <form method="POST" action="{{ route('admin.currencies.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf
            <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-circle-plus text-sky-500 mr-1"></i> {{ __('ui.add_currency') }}</h2>

            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.currency_code') }}</label>
                    <input name="code" required maxlength="3" value="{{ old('code') }}" placeholder="JPY" class="{{ $input }} uppercase">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.currency_name') }}</label>
                    <input name="name" required maxlength="60" value="{{ old('name') }}" placeholder="Japanese Yen" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.currency_symbol') }}</label>
                    <input name="symbol" required maxlength="5" value="{{ old('symbol') }}" placeholder="¥" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.currency_decimals') }}</label>
                    <input type="number" name="decimal_places" min="0" max="4" required value="{{ old('decimal_places', 0) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.currency_rate') }}</label>
                    <input type="number" name="rate_to_base" step="any" min="0" required value="{{ old('rate_to_base') }}" class="{{ $input }}">
                </div>
            </div>

            <button class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                {{ __('ui.save_changes') }}
            </button>
        </form>

        {{-- DAFTAR --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('ui.currencies') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.currency_rate') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.currency_sample') }}</th>
                            <th class="text-right px-3 py-3">{{ __('ui.currency_used') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.account_status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($currencies as $currency)
                            @php $rowError = $errors->getBag('rate_' . $currency->code)->first('rate_to_base'); @endphp
                            <tr class="hover:bg-slate-50/60 align-top">
                                <td class="px-6 py-3">
                                    <div class="font-black text-slate-900">{{ $currency->code }}
                                        @if ($currency->isBase())
                                            <span class="ml-1 px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold">{{ __('ui.currency_base') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $currency->name }} &middot; {{ $currency->symbol }}</div>
                                </td>

                                <td class="px-3 py-3">
                                    @if ($currency->isBase())
                                        <span class="text-slate-500 text-sm">1 {{ $currency->code }} = Rp 1</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.currencies.rate', $currency) }}"
                                              class="rate-form flex items-center gap-2" data-old="{{ $currency->rate_to_base }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="confirm_large" value="0">

                                            <span class="text-xs text-slate-500 whitespace-nowrap">1 {{ $currency->code }} = Rp</span>
                                            <input type="number" name="rate_to_base" step="any" min="0" required
                                                   value="{{ $trim($currency->rate_to_base) }}"
                                                   class="w-32 rounded-xl border {{ $rowError ? 'border-red-300' : 'border-slate-200' }} px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                                            <button class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-xs font-bold transition">
                                                {{ __('ui.currency_save_rate') }}
                                            </button>
                                        </form>
                                        @if ($rowError)
                                            <div class="text-[11px] text-red-600 mt-1 max-w-xs">{{ $rowError }}</div>
                                        @endif
                                        <div class="text-[11px] text-slate-400 mt-1">{{ __('ui.last_changed', ['date' => $currency->updated_at->format('d/m/Y H:i')]) }}</div>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-slate-700 font-semibold whitespace-nowrap">{{ format_price(1000000, $currency->code) }}</td>
                                <td class="px-3 py-3 text-right text-slate-600">{{ $currency->bookings_count }}</td>
                                <td class="px-3 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $currency->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $currency->is_active ? __('ui.account_active') : __('ui.account_inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.currencies.edit', $currency) }}" class="text-sm font-bold text-sky-600 hover:underline">{{ __('ui.edit') }}</a>

                                        @if (! $currency->isBase() && $currency->bookings_count === 0)
                                            <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}"
                                                  onsubmit="return confirm(@js(__('ui.currency_delete_confirm')))">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-bold text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Perubahan kurs yang besar (lebih dari 25%) meminta konfirmasi dulu, untuk mencegah salah ketik
        (function () {
            const message = @js(__('ui.currency_rate_confirm'));

            document.querySelectorAll('.rate-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const previous = parseFloat(form.dataset.old);
                    const next     = parseFloat(form.querySelector('[name="rate_to_base"]').value);

                    if (!isFinite(previous) || !isFinite(next) || previous <= 0 || next <= 0) return;

                    const percent = Math.round(Math.abs(next - previous) / previous * 100);

                    if (percent > 25) {
                        if (!window.confirm(message.replace(':percent', percent))) {
                            event.preventDefault();
                            return;
                        }
                        form.querySelector('[name="confirm_large"]').value = '1';
                    }
                });
            });
        })();
    </script>
@endpush