@extends('layouts.app')

@section('content')
    @php
        $pages = [
            'home'   => ['ui.banner_home', 'fa-house'],
            'hotels' => ['ui.banner_hotels', 'fa-hotel'],
            'cars'   => ['ui.banner_cars', 'fa-car-side'],
        ];
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.banners') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.banners_hint') }}</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            @foreach ($banners as $banner)
                @php
                    [$label, $icon] = $pages[$banner['key']];
                    $error = $errors->getBag('banner_' . $banner['key'])->first('image');
                @endphp

                <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
                    <div class="aspect-[16/6] bg-slate-200 overflow-hidden">
                        <img src="{{ $banner['url'] }}" alt="{{ __($label) }}"
                             onerror="this.onerror=null;this.src='{{ $banner['default'] }}'"
                             class="w-full h-full object-cover">
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('ui.banner_used_on') }}</div>
                                <h2 class="text-lg font-extrabold text-slate-900">
                                    <i class="fa-solid {{ $icon }} text-sky-500 mr-1"></i> {{ __($label) }}
                                </h2>
                            </div>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap
                                         {{ $banner['custom'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $banner['custom'] ? __('ui.banner_custom') : __('ui.banner_default') }}
                            </span>
                        </div>

                        @if ($banner['updated_at'])
                            <p class="text-xs text-slate-500">{{ __('ui.banner_last_updated', ['date' => $banner['updated_at']->format('d/m/Y H:i')]) }}</p>
                        @endif

                        @if ($error)
                            <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">{{ $error }}</div>
                        @endif

                        <form method="POST" action="{{ route('admin.banners.update', $banner['key']) }}" enctype="multipart/form-data"
                              class="space-y-3">
                            @csrf
                            <input type="file" name="image" required accept="image/jpeg,image/png,image/webp"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0
                                          file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100">
                            <p class="text-[11px] text-slate-400">{{ __('ui.banner_size_hint') }}</p>

                            <button class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
                                <i class="fa-solid fa-upload mr-1"></i> {{ __('ui.banner_save') }}
                            </button>
                        </form>

                        @if ($banner['custom'])
                            <form method="POST" action="{{ route('admin.banners.reset', $banner['key']) }}"
                                  onsubmit="return confirm(@js(__('ui.banner_reset_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs font-bold text-red-600 hover:underline">
                                    <i class="fa-solid fa-rotate-left mr-1"></i> {{ __('ui.banner_reset') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection