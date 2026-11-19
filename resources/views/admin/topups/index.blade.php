@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => format_price($value, 'IDR');
        $statusStyle = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'approved'  => 'bg-emerald-100 text-emerald-700',
            'rejected'  => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
        ];
        $tabs = ['pending', 'approved', 'rejected', 'cancelled', 'all'];
        $input = 'rounded-xl border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/30';
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.topup_requests') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.topup_requests_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $tab)
                <a href="{{ route('admin.topups.index', ['status' => $tab]) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === $tab ? 'bg-sagara-navy text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $tab === 'all' ? __('ui.all') : __('ui.topup_status_' . $tab) }}
                    @if ($tab !== 'all') <span class="opacity-70">({{ $counts->get($tab, 0) }})</span> @endif
                </a>
            @endforeach
        </div>

        <div class="space-y-4">
            @forelse ($requests as $topup)
                <div class="bg-white rounded-3xl border {{ $topup->status === 'pending' ? 'border-amber-200' : 'border-slate-100' }} shadow-md p-6 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyle[$topup->status] }}">{{ __('ui.topup_status_' . $topup->status) }}</span>
                                <span class="font-mono text-xs text-slate-500">{{ $topup->code }}</span>
                            </div>
                            <div class="font-extrabold text-slate-900 text-lg mt-1">{{ $topup->company->company_name }}
                                <span class="text-xs font-mono font-semibold text-slate-400">{{ $topup->company->corporate_code }}</span></div>
                            <div class="text-xs text-slate-500">
                                {{ __('ui.topup_requested_by', ['name' => $topup->requester?->email ?? '-', 'date' => $topup->created_at->format('d/m/Y H:i')]) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.topup_amount') }}</div>
                            <div class="text-2xl font-black text-slate-900">{{ $money($topup->amount) }}</div>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-x-6 gap-y-1 text-sm">
                        <div><span class="text-slate-500">{{ __('ui.topup_to_account') }}:</span> <strong>{{ $topup->account ? $topup->account->bank_name . ' ' . $topup->account->account_number : '-' }}</strong></div>
                        <div><span class="text-slate-500">{{ __('ui.payout_paid_on') }}:</span> <strong>{{ $topup->transfer_date->format('d/m/Y') }}</strong></div>
                        <div><span class="text-slate-500">{{ __('ui.topup_sender') }}:</span> <strong>{{ $topup->sender_name ?: '-' }}</strong></div>
                        @if ($topup->note)<div class="sm:col-span-3 text-slate-600">"{{ $topup->note }}"</div>@endif
                        <div class="sm:col-span-3">
                            <a href="{{ route('admin.topups.proof', $topup) }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline">
                                <i class="fa-solid fa-paperclip"></i> {{ __('ui.payout_proof_view') }}
                            </a>
                        </div>
                    </div>

                    @if ($topup->status === 'pending')
                        <div class="grid md:grid-cols-2 gap-4 pt-2">
                            <form method="POST" action="{{ route('admin.topups.approve', $topup) }}" class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 space-y-3">
                                @csrf
                                <div class="font-bold text-emerald-700 text-sm"><i class="fa-solid fa-circle-check"></i> {{ __('ui.topup_approve') }}</div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.topup_received_amount') }}</label>
                                    <input type="number" name="approved_amount" min="1" max="1000000000" required value="{{ $topup->amount }}" class="{{ $input }} w-full">
                                </div>
                                <input name="decision_note" maxlength="500" placeholder="{{ __('ui.topup_note_if_different') }}" class="{{ $input }} w-full">
                                <button class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition"
                                        onclick="return confirm(@js(__('ui.topup_approve_confirm')))">{{ __('ui.topup_approve') }}</button>
                            </form>

                            <form method="POST" action="{{ route('admin.topups.reject', $topup) }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-4 space-y-3">
                                @csrf
                                <div class="font-bold text-red-700 text-sm"><i class="fa-solid fa-ban"></i> {{ __('ui.topup_reject') }}</div>
                                <input name="decision_note" required maxlength="500" placeholder="{{ __('ui.company_reject_reason') }}" class="{{ $input }} w-full">
                                <button class="px-4 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition">{{ __('ui.topup_reject') }}</button>
                            </form>
                        </div>
                    @else
                        <div class="text-xs text-slate-500">
                            @if ($topup->decided_at){{ __('ui.topup_decided', ['name' => $topup->decider?->email ?? '-', 'date' => $topup->decided_at->format('d/m/Y H:i')]) }}@endif
                            @if ($topup->status === 'approved') &middot; <strong class="text-emerald-600">{{ __('ui.topup_credited', ['amount' => $money($topup->approved_amount)]) }}</strong>@endif
                            @if ($topup->decision_note) &middot; {{ $topup->decision_note }}@endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-14 text-center text-slate-400">
                    <i class="fa-solid fa-wallet text-3xl mb-2 block"></i> {{ __('ui.no_results') }}
                </div>
            @endforelse
        </div>

        <div>{{ $requests->links() }}</div>
    </section>
@endsection