@php
    $docs = [
        'tax'        => ['tax_document', $partner->tax_document_path],
        'license'    => ['license_document', $partner->license_document_path],
        'supporting' => ['supporting_document', $partner->supporting_document_path],
    ];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 space-y-3">
    <h2 class="font-extrabold text-slate-900">{{ __('ui.partner_registration_data') }}</h2>

    @if ($partner->source === 'admin')
        <p class="text-xs text-violet-700 font-semibold">
            <i class="fa-solid fa-user-shield"></i>
            {{ __('ui.partner_created_by', ['name' => $partner->creator?->email ?? '-', 'date' => $partner->created_at->format('d/m/Y')]) }}
        </p>
    @endif

    @if ($partner->tax_id || $partner->address)
        <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div><span class="text-slate-500">{{ __('ui.legal_form') }}:</span> <strong>{{ $partner->legal_form ?: '-' }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.business_type') }}:</span> <strong>{{ $partner->business_type ? __('ui.business_type_' . $partner->business_type) : '-' }}</strong></div>

            @if (\App\Support\AdminAbilities::can(auth()->user(), 'operations'))
                <form method="POST" action="{{ route('admin.partners.business-type', $partner) }}" class="sm:col-span-2 flex flex-wrap items-center gap-2 pt-1">
                    @csrf
                    <select name="business_type" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs">
                        @foreach (['hotel', 'car', 'both'] as $type)
                            <option value="{{ $type }}" @selected(($partner->business_type ?? 'both') === $type)>{{ __('ui.business_type_' . $type) }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-xs font-bold transition">{{ __('ui.business_type_admin_label') }}</button>
                </form>
            @endif

            <div><span class="text-slate-500">{{ __('ui.tax_id') }}:</span> <strong class="font-mono">{{ $partner->tax_id ?: '-' }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.business_license_no') }}:</span> <strong class="font-mono">{{ $partner->business_license_no ?: '-' }}</strong></div>
            <div class="sm:col-span-2"><span class="text-slate-500">{{ __('ui.company_address') }}:</span> <strong>{{ $partner->address }}{{ $partner->city ? ', ' . $partner->city->name : '' }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.company_phone') }}:</span> <strong>{{ $partner->phone ?: '-' }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.website') }}:</span>
                @if ($partner->website)<a href="{{ $partner->website }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline break-all">{{ $partner->website }}</a>@else <strong>-</strong>@endif
            </div>
            <div class="sm:col-span-2"><span class="text-slate-500">{{ __('ui.contact_person') }}:</span>
                <strong>{{ $partner->contact_name ?: '-' }}</strong> &middot; {{ $partner->user?->email }} &middot; {{ $partner->user?->phone_number }}</div>
            <div class="sm:col-span-2"><span class="text-slate-500">{{ __('ui.payout_bank_account') }}:</span>
                <strong>{{ $partner->hasBankDetails() ? $partner->bankLabel() : __('ui.payout_no_bank_short') }}</strong></div>
        </div>

        <div class="flex flex-wrap gap-4 text-sm pt-1">
            @foreach ($docs as $type => [$labelKey, $path])
                @if ($path)
                    <a href="{{ route('admin.partners.document', [$partner, $type]) }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline">
                        <i class="fa-solid fa-paperclip"></i> {{ __('ui.' . $labelKey) }}
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">{{ __('ui.partner_legacy_registration') }}</p>
    @endif
</div>