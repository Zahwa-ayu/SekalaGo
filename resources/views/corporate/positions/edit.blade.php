@extends('layouts.app')

@section('content')
    @php
        $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
    @endphp

    <section class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
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

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <form method="POST" action="{{ route('corporate.positions.update', $position) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="{{ $box }}">
                    <label class="{{ $label }}"><i class="fa-solid fa-id-badge text-sagara-blue"></i> {{ __('ui.position_name') }}</label>
                    <input name="name" value="{{ old('name', $position->name) }}" required class="{{ $input }}">
                </div>

                <div class="{{ $box }}">
                    <label class="{{ $label }}"><i class="fa-solid fa-coins text-sagara-blue"></i> {{ __('ui.booking_limit') }}</label>
                    <input type="number" name="booking_limit" min="0" required class="{{ $input }}"
                           value="{{ old('booking_limit', $position->booking_limit) }}">
                </div>

                <p>
                    <label>{{ __('ui.monthly_budget') }}<br>
                        <input type="number" name="monthly_budget" min="0"
                            value="{{ old('monthly_budget', $position->monthly_budget) }}">
                    </label>
                    <small>{{ __('ui.monthly_budget_hint') }}</small>
                </p>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button class="px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}
                    </button>
                    <a href="{{ route('corporate.positions.index') }}" class="px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </section>
@endsection