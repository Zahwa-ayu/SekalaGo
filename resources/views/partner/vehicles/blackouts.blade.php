@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('partner.vehicles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_vehicles') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.blackout_dates') }}: {{ $vehicle->name }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 min-w-0 space-y-6">
                {{-- FORM TAMBAH --}}
                <form method="POST" action="{{ route('partner.vehicles.blackouts.store', $vehicle) }}" class="{{ $card }} space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="block">
                            <span class="{{ $label }}">{{ __('ui.blackout_start') }}</span>
                            <input type="date" name="start_date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}" required class="{{ $input }}">
                        </label>
                        <label class="block">
                            <span class="{{ $label }}">{{ __('ui.blackout_end') }}</span>
                            <input type="date" name="end_date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}" required class="{{ $input }}">
                        </label>
                        <label class="block">
                            <span class="{{ $label }}">{{ __('ui.blackout_note') }}</span>
                            <input name="note" maxlength="255" value="{{ old('note') }}" class="{{ $input }}">
                        </label>
                    </div>

                    <button class="px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                        <i class="fa-solid fa-ban"></i> {{ __('ui.add_blackout') }}
                    </button>
                </form>

                {{-- DAFTAR --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($blackouts as $blackout)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                            {{ $blackout->start_date->format('d/m/Y') }} - {{ $blackout->end_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">{{ $blackout->note ?? '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            @include('partials.delete', [
                                                'action'  => route('partner.blackouts.destroy', $blackout),
                                                'title'   => __('ui.delete') . '?',
                                                'message' => __('ui.delete_confirm'),
                                                'label'   => __('ui.delete'),
                                                'class'   => 'text-xs font-bold text-red-600 hover:underline',
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="px-6 py-12 text-center text-slate-400">{{ __('ui.no_blackouts') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KETERANGAN --}}
            <div class="{{ $card }} text-xs text-slate-600 lg:sticky lg:top-24">
                <p><i class="fa-solid fa-circle-info text-sagara-blue"></i> {{ __('ui.blackout_hint') }}</p>
            </div>
        </div>
    </section>
@endsection