@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $used  = $city->hotels_count + $city->vehicles_count;
    @endphp

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('admin.cities.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                <i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.cities') }}
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $city->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ __('ui.hotels_count') }}: <strong>{{ $city->hotels_count }}</strong>
                &middot; {{ __('ui.vehicles_count') }}: <strong>{{ $city->vehicles_count }}</strong>
            </p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.cities.update', $city) }}" enctype="multipart/form-data"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                    <input name="name" required maxlength="100" value="{{ old('name', $city->name) }}" class="{{ $input }}">
                    @if ($used > 0)
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.city_rename_hint', ['count' => $used]) }}</p>
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
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.city_aliases_hint') }}</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.city_sort') }}</label>
                    <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $city->sort_order) }}" class="{{ $input }}">
                </div>
                <div class="flex flex-wrap items-end gap-5 text-sm font-semibold text-slate-600 pb-2">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $city->is_featured)) class="rounded border-slate-300 text-sky-500"> {{ __('ui.city_featured') }}</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->is_active)) class="rounded border-slate-300 text-sky-500"> {{ __('ui.city_active') }}</label>
                </div>
            </div>

            <div>
                <label class="{{ $label }}">{{ __('ui.city_photo') }}</label>

                @if ($city->imageUrl())
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ $city->imageUrl() }}" alt="" class="w-40 h-24 rounded-xl object-cover border border-slate-200">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-red-600">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-500"> {{ __('ui.city_image_remove') }}
                        </label>
                    </div>
                @endif

                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.city_photo_hint') }}</p>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    {{ __('ui.save_changes') }}
                </button>
                <a href="{{ route('admin.cities.index') }}" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </section>
@endsection