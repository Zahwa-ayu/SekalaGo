@extends('layouts.app')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.add_company') }}</h1>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-sagara-blue/30 focus:border-sagara-blue';
            $label = 'block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5';
        @endphp

        <form method="POST" action="{{ route('admin.companies.store') }}"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-5">
            @csrf

            <div>
                <label class="{{ $label }}">{{ __('ui.company_name') }}</label>
                <input name="company_name" value="{{ old('company_name') }}" required class="{{ $input }}">
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.company_code') }} ({{ __('ui.optional') }})</label>
                <input name="corporate_code" value="{{ old('corporate_code') }}" class="{{ $input }} font-mono">
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="{{ $label }}">{{ __('ui.default_limit') }}</label>
                    <input type="number" name="travel_policy_limit" min="0"
                           value="{{ old('travel_policy_limit', 2000000) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.initial_deposit') }}</label>
                    <input type="number" name="initial_deposit" min="0"
                           value="{{ old('initial_deposit', 0) }}" class="{{ $input }}">
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100">
                <h2 class="text-base font-extrabold text-slate-900 mb-4">{{ __('ui.finance_account') }}</h2>

                <div class="space-y-5">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.full_name') }}</label>
                        <input name="finance_name" value="{{ old('finance_name') }}" required class="{{ $input }}">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">{{ __('ui.email') }}</label>
                            <input type="email" name="finance_email" value="{{ old('finance_email') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ __('ui.temp_password') }}</label>
                            <input type="text" name="finance_password" required class="{{ $input }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button class="px-5 py-2.5 rounded-xl text-sm font-bold bg-sagara-navy text-white shadow-md hover:opacity-90 transition">
                    {{ __('ui.save') }}
                </button>
                <a href="{{ route('admin.companies.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </section>
@endsection