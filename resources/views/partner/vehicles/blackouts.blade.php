@extends('layouts.app')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('partner.vehicles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_vehicles') }}
        </a>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.blackout_dates') }}</h1>
            <p class="text-sm text-slate-500">{{ $vehicle->name }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('partner.vehicles.blackouts.store', $vehicle) }}"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf
            <p class="text-xs text-slate-500"><i class="fa-solid fa-circle-info"></i> {{ __('ui.blackout_hint') }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="block">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.blackout_start') }}</span>
                    <input type="date" name="start_date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                </label>
                <label class="block">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.blackout_end') }}</span>
                    <input type="date" name="end_date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                </label>
                <label class="block">
                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('ui.blackout_note') }}</span>
                    <input name="note" maxlength="255" value="{{ old('note') }}"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue">
                </label>
            </div>

            <button class="px-6 py-2.5 bg-gradient-to-r from-sagara-blue to-sagara-teal text-white text-sm font-bold rounded-xl shadow-lg hover:from-sagara-navy hover:to-sagara-blue transition">
                <i class="fa-solid fa-ban"></i> {{ __('ui.add_blackout') }}
            </button>
        </form>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <table class="w-full text-sm text-left">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($blackouts as $blackout)
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $blackout->start_date->format('d/m/Y') }} - {{ $blackout->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ $blackout->note ?? '-' }}</td>
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="{{ route('partner.blackouts.destroy', $blackout) }}"
                                      onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-bold text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-10 text-center text-slate-400">{{ __('ui.no_blackouts') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection