@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-gradient-to-r from-sagara-navy via-sagara-blue to-sagara-navy rounded-3xl p-8 sm:p-10 text-white shadow-xl">
            <div class="w-12 h-12 rounded-xl bg-sagara-teal/20 flex items-center justify-center text-sagara-teal text-xl mb-4">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">{{ __($title) }}</h1>
            <p class="text-sky-200 text-sm mt-2 flex items-center gap-2">
                <i class="fa-solid fa-circle-user"></i>
                {{ __('ui.logged_in_as', ['email' => auth()->user()->email]) }}
            </p>
        </div>
    </section>
@endsection