@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $th    = 'px-6 py-4 font-bold';
        $td    = 'px-6 py-4';
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.cities') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.cities_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- TAMBAH KOTA --}}
        <form method="POST" action="{{ route('admin.cities.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4 mb-6">
            @csrf
            <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-circle-plus text-sky-500 mr-1"></i> {{ __('ui.add_city') }}</h2>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                    <input name="name" required maxlength="100" value="{{ old('name') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.province') }}</label>
                    <input name="province" maxlength="100" value="{{ old('province') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', 0) }}" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.city_aliases') }}</label>
                <input name="aliases" maxlength="255" value="{{ old('aliases') }}" placeholder="Jogja, Yogya, DIY" class="{{ $input }}">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.city_aliases_hint') }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4 items-end">
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_photo') }}</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.city_photo_hint') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-5 text-sm font-semibold text-slate-600">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="rounded border-slate-300 text-sagara-blue"> {{ __('ui.city_featured') }}</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-sagara-blue"> {{ __('ui.city_active') }}</label>
                </div>
            </div>

            <button class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition">
                {{ __('ui.save_changes') }}
            </button>
        </form>

        {{-- DAFTAR --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <form method="GET" action="{{ route('admin.cities.index') }}" class="px-6 py-4 border-b border-slate-100 flex flex-wrap gap-2 items-center">
                <input name="q" value="{{ $search }}" placeholder="{{ __('ui.city_search') }}"
                       class="flex-1 min-w-48 rounded-xl border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">{{ __('ui.search') }}</button>
                @if ($search !== '')
                    <a href="{{ route('admin.cities.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">{{ __('ui.reset') }}</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="{{ $th }}">{{ __('ui.cities') }}</th>
                            <th class="{{ $th }}">{{ __('ui.city_aliases') }}</th>
                            <th class="{{ $th }}">{{ __('ui.hotels_count') }}</th>
                            <th class="{{ $th }}">{{ __('ui.vehicles_count') }}</th>
                            <th class="{{ $th }}">{{ __('ui.account_status') }}</th>
                            <th class="{{ $th }}">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cities as $city)
                            @php $used = $city->hotels_count + $city->vehicles_count > 0; @endphp
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="{{ $td }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-9 rounded-lg bg-slate-200 overflow-hidden shrink-0">
                                            @if ($city->imageUrl())
                                                <img src="{{ $city->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">
                                                {{ $city->name }}
                                                @if ($city->is_featured)<i class="fa-solid fa-star text-amber-400 text-xs ml-1" title="{{ __('ui.city_featured') }}"></i>@endif
                                            </div>
                                            <div class="text-[11px] text-slate-500">{{ $city->province ?: '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="{{ $td }} text-xs text-slate-500 max-w-56">{{ $city->aliases ?: '-' }}</td>
                                <td class="{{ $td }} text-slate-600">{{ $city->hotels_count }}</td>
                                <td class="{{ $td }} text-slate-600">{{ $city->vehicles_count }}</td>
                                <td class="{{ $td }}">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $city->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $city->is_active ? __('ui.account_active') : __('ui.account_inactive') }}
                                    </span>
                                </td>
                                <td class="{{ $td }} whitespace-nowrap text-xs font-bold">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('admin.cities.edit', $city) }}" class="text-sagara-blue hover:underline">{{ __('ui.edit') }}</a>

                                        @unless ($used)
                                            <form method="POST" action="{{ route('admin.cities.destroy', $city) }}"
                                                  onsubmit="return confirm(@js(__('ui.city_delete_confirm')))">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-location-dot text-3xl mb-2 block"></i>
                                    {{ __('ui.no_results') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $cities->links() }}
            </div>
        </div>
    </section>
@endsection