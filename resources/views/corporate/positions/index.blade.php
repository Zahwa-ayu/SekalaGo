@extends('layouts.app')

@section('content')
    @php
        $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
        $btn = 'px-6 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2';
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.positions_limits') }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- BATAS DEFAULT --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <h2 class="text-lg font-extrabold text-slate-900 mb-4"><i class="fa-solid fa-shield-halved text-sagara-blue"></i> {{ __('ui.default_limit') }}</h2>

                <form method="POST" action="{{ route('corporate.policy.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-coins text-sagara-blue"></i> {{ __('ui.default_limit') }} (IDR)</label>
                        <input type="number" name="travel_policy_limit" min="0" required class="{{ $input }}"
                               value="{{ old('travel_policy_limit', $company->travel_policy_limit) }}">
                    </div>
                    <button class="{{ $btn }} w-full"><i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}</button>
                </form>
                <p class="text-[11px] text-slate-500 mt-3"><i class="fa-solid fa-circle-info"></i> {{ __('ui.default_limit_hint') }}</p>
            </div>

            {{-- TAMBAH JABATAN --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6">
                <h2 class="text-lg font-extrabold text-slate-900 mb-4"><i class="fa-solid fa-plus text-sagara-blue"></i> {{ __('ui.add_position') }}</h2>

                <form method="POST" action="{{ route('corporate.positions.store') }}" class="space-y-4">
                    @csrf
                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-id-badge text-sagara-blue"></i> {{ __('ui.position_name') }}</label>
                        <input name="name" value="{{ old('name') }}" placeholder="{{ __('ui.position_name') }}" required class="{{ $input }}">
                    </div>
                    <div class="{{ $box }}">
                        <label class="{{ $label }}"><i class="fa-solid fa-coins text-sagara-blue"></i> {{ __('ui.booking_limit') }}</label>
                        <input type="number" name="booking_limit" min="0" value="{{ old('booking_limit') }}"
                               placeholder="{{ __('ui.booking_limit') }}" required class="{{ $input }}">
                    </div>
                    <button class="{{ $btn }} w-full"><i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}</button>
                </form>
            </div>
        </div>

        {{-- DAFTAR JABATAN --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.position_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.booking_limit') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.employees_count') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($positions as $position)
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $position->name }}</td>
                                <td class="px-6 py-4 font-bold text-sagara-blue whitespace-nowrap">{{ format_price($position->booking_limit, 'IDR') }}</td>
                                <td class="px-6 py-4 text-slate-600"><i class="fa-solid fa-users text-slate-400"></i> {{ $position->members_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('corporate.positions.edit', $position) }}"
                                           class="px-3 py-1.5 text-xs font-bold border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                                            <i class="fa-solid fa-pen"></i> {{ __('ui.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('corporate.positions.destroy', $position) }}"
                                              class="inline"
                                              onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <i class="fa-solid fa-trash"></i> {{ __('ui.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-id-badge text-3xl mb-2 block"></i>
                                    {{ __('ui.no_positions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection