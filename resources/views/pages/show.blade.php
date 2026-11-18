@extends('layouts.app')

@section('solid_nav', '1')

@section('content')
    @php
        $paragraphs = preg_split('/\R{2,}/', trim($page->body)) ?: [];
        $extras     = $page->extras;
    @endphp

    <div class="bg-slate-50 text-slate-800 min-h-screen font-sans antialiased">

    @if ($page->template === 'about')
        {{-- HERO: gradient biru polos --}}
        <section class="bg-gradient-to-br from-sky-500 to-sky-700 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.15]">{{ $page->title }}</h1>
                <div class="w-14 h-1 rounded-full bg-white/70 mx-auto mt-6"></div>
                @if (count($paragraphs))
                    <p class="mt-6 text-base sm:text-lg text-white/90 leading-relaxed">{!! nl2br(e($paragraphs[0])) !!}</p>
                @endif
            </div>
        </section>

        {{-- PARAGRAF LANJUTAN --}}
        @if (count($paragraphs) > 1)
            <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-10 space-y-4 text-sm sm:text-base leading-relaxed text-slate-600">
                    @foreach (array_slice($paragraphs, 1) as $paragraph)
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- KARTU ANGKA --}}
        @if (! empty($extras['stats']))
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
                @if (! empty($extras['stats_title']))
                    <div class="text-center mb-10">
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $extras['stats_title'] }}</h2>
                        <div class="w-12 h-1 rounded-full bg-sky-500 mx-auto mt-3"></div>
                    </div>
                @endif
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach ($extras['stats'] as $stat)
                        <div class="flex-1 min-w-[9rem] max-w-[14rem] bg-white rounded-2xl border border-slate-200/80 hover:border-sky-400 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6 text-center">
                            <div class="text-3xl sm:text-4xl font-black text-sky-600">{{ $stat['value'] }}</div>
                            <div class="mt-2 text-sm text-slate-500">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- POIN KEUNGGULAN --}}
        @if (! empty($extras['highlights']))
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                @if (! empty($extras['highlights_title']))
                    <div class="text-center mb-10">
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $extras['highlights_title'] }}</h2>
                        <div class="w-12 h-1 rounded-full bg-sky-500 mx-auto mt-3"></div>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($extras['highlights'] as $highlight)
                        <div class="rounded-2xl bg-white border border-slate-200/80 hover:border-sky-400 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-500 group-hover:text-white flex items-center justify-center mb-4 border border-sky-100 transition duration-300 shadow-sm">
                                <i class="fa-solid fa-circle-check text-lg"></i>
                            </div>
                            <h3 class="font-bold text-base mb-2 text-slate-900 group-hover:text-sky-600 transition leading-snug">{{ $highlight['title'] }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">{!! nl2br(e($highlight['text'])) !!}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @else
        {{-- TEKS BIASA (Syarat, Privasi, dll.) --}}
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $page->title }}</h1>
            <div class="w-12 h-1 rounded-full bg-sky-500 mt-3"></div>

            <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-10 space-y-4 text-sm sm:text-base leading-relaxed text-slate-600">
                @foreach ($paragraphs as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach
            </div>
        </section>
    @endif

    </div>
@endsection