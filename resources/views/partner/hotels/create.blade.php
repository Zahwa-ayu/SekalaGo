@extends('layouts.app')

@section('content')
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('partner.hotels.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.my_hotels') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.add_hotel') }}</h1>

        <form method="POST" action="{{ route('partner.hotels.store') }}" class="space-y-6">
            @csrf

            <div class="grid lg:grid-cols-3 gap-6 items-start">
                {{-- KOLOM KIRI: ISIAN --}}
                <div class="lg:col-span-2 space-y-6">
                    @include('partner.hotels._form')

                    {{-- AKSI --}}
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-check"></i> {{ __('ui.save') }}
                        </button>
                        <a href="{{ route('partner.hotels.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                            {{ __('ui.cancel') }}
                        </a>
                    </div>
                </div>

                {{-- KOLOM KANAN: KETERANGAN --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-2 text-xs text-slate-600 lg:sticky lg:top-24">
                    <div class="font-extrabold text-slate-900 text-sm"><i class="fa-solid fa-circle-info text-sagara-blue"></i> {{ __('ui.location') }}</div>
                    <p>{{ __('ui.coordinates_hint') }}</p>
                </div>
            </div>
        </form>
    </section>
@endsection