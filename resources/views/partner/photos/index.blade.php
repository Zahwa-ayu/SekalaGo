@extends('layouts.app')

@section('content')
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}
        </a>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.manage_photos') }}</h1>
            <p class="text-sm text-slate-500">{{ $title }}</p>
        </div>

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
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                <i class="fa-solid fa-images text-sagara-blue"></i> {{ __('ui.upload_photos') }} ({{ $photos->count() }}/{{ $max }})
            </div>

            <input type="file" name="photos[]" multiple required accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-sky-100 file:px-4 file:py-2 file:font-bold file:text-sagara-blue hover:file:bg-sky-200">

            <p class="text-xs text-slate-500"><i class="fa-solid fa-circle-info"></i> {{ __('ui.photo_hint') }}</p>

            <button class="px-6 py-2.5 bg-gradient-to-r from-sagara-blue to-sagara-teal text-white text-sm font-bold rounded-xl shadow-lg hover:from-sagara-navy hover:to-sagara-blue transition">
                <i class="fa-solid fa-upload"></i> {{ __('ui.upload_photos') }}
            </button>
        </form>

        {{-- GALERI --}}
        @if ($photos->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md px-6 py-12 text-center text-slate-400">
                <i class="fa-solid fa-image text-3xl mb-2 block"></i>
                {{ __('ui.no_photos') }}
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($photos as $photo)
                    <div class="bg-white rounded-2xl border {{ $photo->is_cover ? 'border-sagara-teal ring-2 ring-sagara-teal/40' : 'border-slate-100' }} shadow-md overflow-hidden">
                        <div class="relative aspect-[4/3] bg-slate-200">
                            <img src="{{ $photo->url() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @if ($photo->is_cover)
                                <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full bg-sagara-teal text-sagara-dark text-[11px] font-bold">
                                    <i class="fa-solid fa-star"></i> {{ __('ui.cover') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2 px-3 py-2.5 text-xs font-bold">
                            @if (! $photo->is_cover)
                                <form method="POST" action="{{ route('partner.photos.cover', $photo) }}">
                                    @csrf
                                    <button class="text-sagara-blue hover:underline">{{ __('ui.set_cover') }}</button>
                                </form>
                            @else
                                <span></span>
                            @endif

                            <form method="POST" action="{{ route('partner.photos.destroy', $photo) }}"
                                  onsubmit="return confirm(@js(__('ui.delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection