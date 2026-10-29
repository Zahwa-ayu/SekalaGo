@extends('layouts.app')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6">{{ __('ui.add_hotel') }}</h1>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <form method="POST" action="{{ route('partner.hotels.store') }}" class="space-y-5">
                @csrf
                @include('partner.hotels._form')

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button class="px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}
                    </button>
                    <a href="{{ route('partner.hotels.index') }}" class="px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </section>
@endsection