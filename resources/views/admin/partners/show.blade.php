@extends('layouts.app')

@section('content')
    @php
        $badge = match ($partner->verification_status) {
            'verified' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
        $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
        $btn = 'px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2';
        $sendLabel = $partner->verification_status === 'verified' ? __('ui.mou_resend_btn') : __('ui.mou_send_btn');
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.verify_partners') }}
        </a>

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $partner->company_name }}</h1>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $badge }}">{{ __('ui.verification_' . $partner->verification_status) }}</span>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- DETAIL PARTNER --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            @if ($partner->verification_note)
                <div class="mb-6 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm">
                    <strong>{{ __('ui.verification_note') }}:</strong> {{ $partner->verification_note }}
                </div>
            @endif

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                @foreach ([
                    ['fa-envelope', __('ui.email'), $partner->user->email],
                    ['fa-phone', __('ui.phone'), $partner->user->phone_number ?? '-'],
                    ['fa-id-card', __('ui.business_license_no'), $partner->business_license_no],
                    ['fa-building-columns', __('ui.bank_account_no'), $partner->bank_account_no ?? '-'],
                    ['fa-calendar-days', __('ui.registered_at'), $partner->created_at->format('d/m/Y H:i')],
                    ['fa-hotel', __('ui.my_hotels'), $partner->hotels_count],
                ] as [$icon, $lbl, $value])
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid {{ $icon }} text-sagara-blue"></i> {{ $lbl }}</dt>
                        <dd class="font-semibold text-slate-800 break-words">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- MOU --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <h2 class="text-lg font-extrabold text-slate-900 mb-4"><i class="fa-solid fa-file-signature text-sagara-blue"></i> {{ __('ui.mou_title') }}</h2>

            @if ($agreement)
                <div class="flex flex-wrap items-center gap-2 mb-5">
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $agreement->status === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sagara-blue' }}">
                        {{ __('ui.mou_status_' . $agreement->status) }}
                    </span>
                    <span class="font-mono text-xs text-slate-500">{{ $agreement->documentNumber() }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.commission_rate') }}</dt>
                        <dd class="font-black text-xl text-sagara-blue">{{ $agreement->commissionPercent() }}%</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.payout_schedule') }}</dt>
                        <dd class="font-semibold text-slate-800">{{ __('ui.payout_' . $agreement->payout_schedule) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.free_cancel_days') }}</dt>
                        <dd class="font-semibold text-slate-800">{{ $agreement->free_cancel_days }}</dd>
                    </div>
                </dl>

                @if ($agreement->status === 'signed')
                    <div class="mt-5 pt-5 border-t border-slate-100 space-y-3 text-sm">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('ui.signed_by') }}</span>
                            <span class="font-semibold text-slate-800">{{ $agreement->signed_name }}</span>
                            <span class="text-slate-500">• {{ $agreement->signed_at->format('d/m/Y H:i') }} ({{ $agreement->signed_ip }})</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('ui.document_integrity') }}</span>
                            @if ($agreement->hashIsValid() === null)
                                <span class="text-slate-500">-</span>
                            @elseif ($agreement->hashIsValid())
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700"><i class="fa-solid fa-circle-check"></i> {{ __('ui.integrity_valid') }}</span>
                            @else
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-700"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.integrity_invalid') }}</span>
                            @endif
                            @if ($agreement->signature_hash)
                                <p class="font-mono text-[11px] text-slate-500 break-all mt-2">SHA-256: {{ $agreement->signature_hash }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-5">
                    <a href="{{ route('admin.partners.agreement.download', $partner) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-xl transition">
                        <i class="fa-solid fa-file-pdf"></i>
                        {{ $agreement->status === 'signed' ? __('ui.download_signed_pdf') : __('ui.download_pdf') }}
                    </a>
                </div>

                <details class="mt-5 group border border-slate-200 rounded-2xl">
                    <summary class="cursor-pointer px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 rounded-2xl">{{ __('ui.mou_content') }}</summary>
                    <pre class="whitespace-pre-wrap text-xs text-slate-600 p-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl font-sans leading-relaxed">{{ $agreement->content }}</pre>
                </details>
            @else
                <p class="text-sm text-slate-500 flex items-center gap-2"><i class="fa-solid fa-circle-info text-slate-400"></i> {{ __('ui.mou_not_sent') }}</p>
            @endif

            @if (! $agreement || $agreement->status !== 'signed')
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 mb-4">{{ $sendLabel }}</h3>

                    <form method="POST" action="{{ route('admin.partners.agreement', $partner) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-percent text-sagara-blue"></i> {{ __('ui.commission_rate') }} (10 - 25)</label>
                                <input type="number" name="commission_percent" min="10" max="25" step="0.5" required class="{{ $input }}"
                                       value="{{ old('commission_percent', $agreement?->commissionPercent() ?? 15) }}">
                            </div>
                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-money-bill-transfer text-sagara-blue"></i> {{ __('ui.payout_schedule') }}</label>
                                <select name="payout_schedule" class="{{ $input }} cursor-pointer">
                                    @foreach (['weekly', 'biweekly', 'monthly'] as $option)
                                        <option value="{{ $option }}"
                                            @selected(old('payout_schedule', $agreement?->payout_schedule ?? 'monthly') === $option)>
                                            {{ __('ui.payout_' . $option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="{{ $box }}">
                                <label class="{{ $label }}"><i class="fa-solid fa-calendar-xmark text-sagara-coral"></i> {{ __('ui.free_cancel_days') }}</label>
                                <input type="number" name="free_cancel_days" min="0" max="30" required class="{{ $input }}"
                                       value="{{ old('free_cancel_days', $agreement?->free_cancel_days ?? 3) }}">
                            </div>
                        </div>
                        <button class="{{ $btn }}"><i class="fa-solid fa-paper-plane"></i> {{ $sendLabel }}</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- KEPUTUSAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <h2 class="text-lg font-extrabold text-slate-900 mb-4">{{ __('ui.decision') }}</h2>

            @if ($partner->verification_status === 'rejected')
                <form method="POST" action="{{ route('admin.partners.reopen', $partner) }}">
                    @csrf
                    <button class="{{ $btn }}"><i class="fa-solid fa-rotate-left"></i> {{ __('ui.reopen') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.partners.reject', $partner) }}" class="space-y-3">
                    @csrf
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-coral transition">
                        <label class="{{ $label }}"><i class="fa-solid fa-comment-dots text-sagara-coral"></i> {{ __('ui.rejection_reason_label') }}</label>
                        <textarea name="verification_note" rows="3" maxlength="500" required
                                  placeholder="{{ __('ui.rejection_reason_label') }}"
                                  class="w-full bg-transparent text-slate-800 text-sm focus:outline-none resize-y">{{ old('verification_note') }}</textarea>
                    </div>
                    <button class="px-8 py-3 bg-gradient-to-r from-sagara-coral to-red-500 hover:from-red-600 hover:to-red-500 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark"></i> {{ __('ui.reject') }}
                    </button>
                </form>
            @endif
        </div>
    </section>
@endsection