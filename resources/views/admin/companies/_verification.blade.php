@php
    $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $pic   = $company->companyUsers->firstWhere('role_in_company', 'admin_finance');
    $tone  = ['pending' => 'border-amber-200 bg-amber-50', 'verified' => 'border-emerald-200 bg-emerald-50', 'rejected' => 'border-red-200 bg-red-50'][$company->verification_status];
@endphp

<div class="rounded-3xl border {{ $tone }} p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-extrabold text-slate-900">{{ __('ui.company_verification') }}</h2>
        <span class="text-xs font-bold text-slate-600">{{ __('ui.company_status_' . $company->verification_status) }}
            @if ($company->source === 'self') &middot; {{ __('ui.company_self_registered') }} @endif
        </span>
    </div>

    @if ($company->tax_id)
        <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div><span class="text-slate-500">{{ __('ui.legal_form') }}:</span> <strong>{{ $company->legal_form }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.tax_id') }}:</span> <strong class="font-mono">{{ $company->tax_id }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.business_license_no') }}:</span> <strong class="font-mono">{{ $company->business_license_no }}</strong></div>
            <div><span class="text-slate-500">{{ __('ui.company_phone') }}:</span> <strong>{{ $company->phone }}</strong></div>
            <div class="sm:col-span-2"><span class="text-slate-500">{{ __('ui.company_address') }}:</span> <strong>{{ $company->address }}, {{ $company->city?->name }}</strong></div>
            @if ($company->industry)<div><span class="text-slate-500">{{ __('ui.industry') }}:</span> <strong>{{ $company->industry }}</strong></div>@endif
            @if ($pic)
                <div class="sm:col-span-2"><span class="text-slate-500">{{ __('ui.corporate_account') }}:</span>
                    <strong>{{ $pic->full_name }}</strong> &middot; {{ $pic->user?->email }} &middot; {{ $pic->user?->phone_number }}</div>
            @endif
        </div>

        <div class="flex flex-wrap gap-4 text-sm">
            @if ($company->tax_document_path)
                <a href="{{ route('admin.companies.document', [$company, 'tax']) }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.tax_document') }}</a>
            @endif
            @if ($company->license_document_path)
                <a href="{{ route('admin.companies.document', [$company, 'license']) }}" target="_blank" rel="noopener" class="font-bold text-sky-600 hover:underline"><i class="fa-solid fa-paperclip"></i> {{ __('ui.license_document') }}</a>
            @endif
        </div>

        @php
            $missingDocs = collect(['tax_document' => $company->tax_document_path, 'license_document' => $company->license_document_path])
                ->filter(fn ($path) => blank($path))->keys();
        @endphp

        @if ($missingDocs->isNotEmpty() && \App\Support\AdminAbilities::can(auth()->user(), 'operations'))
            <form method="POST" action="{{ route('admin.companies.documents.store', $company) }}" enctype="multipart/form-data"
                  class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 space-y-2">
                @csrf
                <div class="text-xs font-bold text-amber-800"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('ui.company_documents_missing') }}</div>
                <div class="flex flex-wrap items-end gap-3">
                    @foreach ($missingDocs as $name)
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">{{ __('ui.' . $name) }}</label>
                            <input type="file" name="{{ $name }}" accept=".pdf,image/jpeg,image/png,image/webp" class="text-xs">
                        </div>
                    @endforeach
                    <button class="px-4 py-2 rounded-xl bg-white border border-amber-300 text-amber-800 hover:bg-amber-100 text-xs font-bold transition">{{ __('ui.save_changes') }}</button>
                </div>
            </form>
        @endif
    @endif

    @if ($company->verification_status === 'pending')
        @if (\App\Support\AdminAbilities::can(auth()->user(), 'operations'))
            <div class="grid md:grid-cols-2 gap-4 pt-2">
                <form method="POST" action="{{ route('admin.companies.verify', $company) }}" class="bg-white rounded-2xl border border-emerald-100 p-4 space-y-3">
                    @csrf
                    <div class="font-bold text-emerald-700 text-sm"><i class="fa-solid fa-circle-check"></i> {{ __('ui.company_approve') }}</div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.travel_policy_limit_label') }}</label>
                        <input type="number" name="travel_policy_limit" min="0" required value="{{ old('travel_policy_limit', $company->travel_policy_limit) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.default_monthly_budget_label') }}</label>
                        <input type="number" name="default_monthly_budget" min="0" value="{{ old('default_monthly_budget', $company->default_monthly_budget) }}" class="{{ $input }}">
                    </div>
                    <button class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition">{{ __('ui.company_approve') }}</button>
                </form>

                <form method="POST" action="{{ route('admin.companies.reject', $company) }}" class="bg-white rounded-2xl border border-red-100 p-4 space-y-3">
                    @csrf
                    <div class="font-bold text-red-700 text-sm"><i class="fa-solid fa-ban"></i> {{ __('ui.company_reject') }}</div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.company_reject_reason') }}</label>
                        <textarea name="verification_note" required maxlength="500" rows="3" class="{{ $input }}">{{ old('verification_note') }}</textarea>
                    </div>
                    <button class="px-4 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-bold transition">{{ __('ui.company_reject') }}</button>
                </form>
            </div>
        @endif
    @elseif ($company->verification_status === 'rejected')
        <div class="text-sm text-red-700"><strong>{{ __('ui.company_reject_reason') }}:</strong> {{ $company->verification_note }}</div>
        @if (\App\Support\AdminAbilities::can(auth()->user(), 'operations'))
            <form method="POST" action="{{ route('admin.companies.reopen', $company) }}">
                @csrf
                <button class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.company_reopen') }}</button>
            </form>
        @endif
    @elseif ($company->verified_at)
        <div class="text-xs text-slate-600">{{ __('ui.company_verified_on', ['date' => $company->verified_at->format('d/m/Y H:i'), 'name' => $company->verifier?->email ?? '-']) }}</div>
    @endif
</div>