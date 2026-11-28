@extends('layouts.app')

@section('content')
    @php
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('corporate.positions.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.positions_limits') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.edit_position') }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('corporate.positions.update', $position) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
                    <div>
                        <label for="name" class="{{ $label }}">{{ __('ui.position_name') }}</label>
                        <input id="name" name="name" value="{{ old('name', $position->name) }}" required class="{{ $input }}">
                    </div>

                    <div>
                        <label for="booking_limit" class="{{ $label }}">{{ __('ui.booking_limit') }}</label>
                        <input id="booking_limit" type="number" name="booking_limit" min="0" required class="{{ $input }}"
                               value="{{ old('booking_limit', $position->booking_limit) }}">
                    </div>

                    <div>
                        <label for="monthly_budget" class="{{ $label }}">{{ __('ui.monthly_budget') }}</label>
                        <input id="monthly_budget" type="number" name="monthly_budget" min="0" class="{{ $input }}"
                               value="{{ old('monthly_budget', $position->monthly_budget) }}">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.monthly_budget_hint') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    <i class="fa-solid fa-check"></i> {{ __('ui.save') }}
                </button>
                <a href="{{ route('corporate.positions.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </section>
@endsection