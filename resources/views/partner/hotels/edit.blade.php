@extends('layouts.app')

@section('content')
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
        <a href="{{ route('partner.hotels.room-types.index', $hotel) }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.room_types') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.edit_room_type') }}: {{ $hotel->name }}</h1>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
            <form method="POST" action="{{ route('partner.room-types.update', $roomType) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('partner.room-types._form')

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button class="px-8 py-3 bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> {{ __('ui.save') }}
                    </button>
                    <a href="{{ route('partner.hotels.room-types.index', $hotel) }}" class="px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </section>
@endsection