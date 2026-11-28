@extends('layouts.app')

@section('content')
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words">{{ __('ui.manage_photos') }}: {{ $title }}</h1>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- UNGGAH --}}
        <form method="POST" action="{{ $storeUrl }}" enctype="multipart/form-data"
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf

            <h2 class="font-extrabold text-slate-900">
                <i class="fa-solid fa-images text-sagara-blue"></i> {{ __('ui.upload_photos') }}
                <span class="text-xs font-semibold text-slate-400">({{ $photos->count() }}/{{ $max }})</span>
            </h2>

            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <input type="file" name="photos[]" multiple required accept="image/jpeg,image/png,image/webp"
                       class="flex-1 min-w-0 text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-sky-50 file:px-4 file:py-2.5 file:font-bold file:text-sky-700 hover:file:bg-sky-100">

                <button class="shrink-0 px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    <i class="fa-solid fa-upload"></i> {{ __('ui.upload_photos') }}
                </button>
            </div>

            <p class="text-xs text-slate-500"><i class="fa-solid fa-circle-info"></i> {{ __('ui.photo_hint') }}</p>
        </form>

        {{-- GALERI --}}
        @if ($photos->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-12 text-center text-slate-400 text-sm">
                <i class="fa-solid fa-image text-3xl mb-2 block"></i>
                {{ __('ui.no_photos') }}
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($photos as $photo)
                    <div class="bg-white rounded-2xl border {{ $photo->is_cover ? 'border-sky-500 ring-2 ring-sky-500/30' : 'border-slate-100' }} shadow-md overflow-hidden">
                        <div class="relative aspect-[4/3] bg-slate-200">
                            <img src="{{ $photo->url() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @if ($photo->is_cover)
                                <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full bg-sky-500 text-white text-[11px] font-bold">
                                    <i class="fa-solid fa-star"></i> {{ __('ui.cover') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-xs font-bold">
                            @if (! $photo->is_cover)
                                <form method="POST" action="{{ route('partner.photos.cover', $photo) }}">
                                    @csrf
                                    <button class="font-bold text-sky-600 hover:underline">{{ __('ui.set_cover') }}</button>
                                </form>
                            @else
                                <span></span>
                            @endif

                            @include('partials.delete', [
                                'action'  => route('partner.photos.destroy', $photo),
                                'title'   => __('ui.delete') . '?',
                                'message' => __('ui.delete_confirm'),
                                'label'   => __('ui.delete'),
                                'class'   => 'text-xs font-bold text-red-600 hover:underline',
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection