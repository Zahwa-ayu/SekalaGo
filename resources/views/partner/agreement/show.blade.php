@extends('layouts.app')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.mou_title') }}</h1>

        @if (! $agreement)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                <i class="fa-solid fa-file-signature text-3xl mb-2 block"></i>
                {{ __('ui.mou_not_sent') }}
            </div>
        @else
            {{-- HEADER --}}
            <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-6 sm:p-8 text-white shadow-xl">
                <div class="flex flex-wrap items-center gap-2 mb-5">
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $agreement->status === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-white/90 text-sagara-blue' }}">
                        {{ __('ui.mou_status_' . $agreement->status) }}
                    </span>
                    <span class="font-mono text-xs text-sky-200">{{ $agreement->documentNumber() }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-5 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-sky-200 uppercase tracking-wider mb-1">{{ __('ui.commission_rate') }}</dt>
                        <dd class="text-3xl font-black">{{ $agreement->commissionPercent() }}%</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-sky-200 uppercase tracking-wider mb-1">{{ __('ui.payout_schedule') }}</dt>
                        <dd class="font-bold">{{ __('ui.payout_' . $agreement->payout_schedule) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-sky-200 uppercase tracking-wider mb-1">{{ __('ui.free_cancel_days') }}</dt>
                        <dd class="font-bold">{{ $agreement->free_cancel_days }}</dd>
                    </div>
                </dl>
            </div>

            {{-- ISI MOU --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
                <a href="{{ route('partner.agreement.download') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-xl transition mb-5">
                    <i class="fa-solid fa-file-pdf"></i>
                    {{ $agreement->status === 'signed' ? __('ui.download_signed_pdf') : __('ui.download_pdf') }}
                </a>

                <pre class="whitespace-pre-wrap text-xs text-slate-600 p-4 border border-slate-200 bg-slate-50 rounded-2xl font-sans leading-relaxed max-h-96 overflow-y-auto">{{ $agreement->content }}</pre>
            </div>

            @if ($agreement->status === 'sent')
                {{-- FORM TANDA TANGAN --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
                    @if ($errors->any())
                        <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('partner.agreement.sign') }}" class="space-y-4">
                        @csrf

                        <label class="flex items-start gap-3 text-sm text-slate-700 cursor-pointer">
                            <input type="checkbox" name="agree" value="1" required class="mt-0.5 rounded text-sagara-blue focus:ring-sagara-blue">
                            <span>{{ __('ui.mou_agree') }}</span>
                        </label>

                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                <i class="fa-solid fa-signature text-sagara-blue"></i> {{ __('ui.mou_signed_name') }}
                            </label>
                            <input name="signed_name" value="{{ old('signed_name') }}"
                                   placeholder="{{ __('ui.mou_signed_name') }}" required
                                   class="w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none">
                        </div>

                        <button class="w-full py-3.5 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition duration-300 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen-nib"></i> {{ __('ui.mou_sign_btn') }}
                        </button>
                    </form>
                </div>
            @elseif ($agreement->status === 'signed')
                <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>
                        <strong>{{ __('ui.signed_by') }}:</strong> {{ $agreement->signed_name }},
                        {{ $agreement->signed_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            @endif
        @endif
    </section>
@endsection