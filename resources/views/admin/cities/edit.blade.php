@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $chip  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold';
        $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $hint  = 'text-[11px] text-slate-400 mt-1';
        $used  = $city->hotels_count + $city->vehicles_count;
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.cities.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.cities') }}
        </a>

        {{-- HEADER --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words min-w-0">{{ $city->name }}</h1>
            <div class="flex flex-wrap gap-2">
                <span class="{{ $chip }}"><i class="fa-solid fa-hotel text-sagara-blue"></i> {{ __('ui.hotels_count') }}: {{ $city->hotels_count }}</span>
                <span class="{{ $chip }}"><i class="fa-solid fa-car-side text-sagara-blue"></i> {{ __('ui.vehicles_count') }}: {{ $city->vehicles_count }}</span>
            </div>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.cities.update', $city) }}" enctype="multipart/form-data"
              class="grid lg:grid-cols-3 gap-6 items-start">
            @csrf
            @method('PUT')

            {{-- KOLOM KIRI: DATA KOTA --}}
            <div class="lg:col-span-2 {{ $card }} space-y-5">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                        <input name="name" required maxlength="100" value="{{ old('name', $city->name) }}" class="{{ $input }}">
                        @if ($used > 0)
                            <p class="{{ $hint }}">{{ __('ui.city_rename_hint', ['count' => $used]) }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="{{ $label }}">{{ __('ui.province') }}</label>
                        <input name="province" maxlength="100" value="{{ old('province', $city->province) }}" class="{{ $input }}">
                    </div>
                </div>

                <div>
                    <label class="{{ $label }}">{{ __('ui.city_aliases') }}</label>
                    <input name="aliases" maxlength="255" value="{{ old('aliases', $city->aliases) }}" class="{{ $input }}">
                    <p class="{{ $hint }}">{{ __('ui.city_aliases_hint') }}</p>
                </div>

                <div>
                    <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $city->sort_order) }}" class="{{ $input }} sm:max-w-48">
                </div>

                <div class="flex flex-wrap gap-x-6 gap-y-3 pt-5 border-t border-slate-100 text-sm font-semibold text-slate-600">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $city->is_featured)) class="rounded border-slate-300 text-sagara-blue">
                        {{ __('ui.city_featured') }}
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->is_active)) class="rounded border-slate-300 text-sagara-blue">
                        {{ __('ui.city_active') }}
                    </label>
                </div>
            </div>

            {{-- KOLOM KANAN: FOTO + AKSI --}}
            <div class="{{ $card }} space-y-5 lg:sticky lg:top-24">
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_photo') }}</label>

                    @if ($city->imageUrl())
                        <div class="mb-3 space-y-2">
                            <img src="{{ $city->imageUrl() }}" alt="{{ $city->name }}" class="w-full h-40 rounded-xl object-cover border border-slate-200">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 cursor-pointer">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-500"> {{ __('ui.city_image_remove') }}
                            </label>
                        </div>
                    @endif

                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold hover:file:bg-slate-200">
                    <p class="{{ $hint }}">{{ __('ui.city_photo_hint') }}</p>
                </div>

                <div class="space-y-2 pt-5 border-t border-slate-100">
                    <button class="w-full px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-check"></i> {{ __('ui.save_changes') }}
                    </button>
                    <a href="{{ route('admin.cities.index') }}" class="block w-full text-center px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                        {{ __('ui.cancel') }}
                    </a>
                </div>
            </div>
        </form>
    </section>
@endsection