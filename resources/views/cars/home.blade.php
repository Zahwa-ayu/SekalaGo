@extends('layouts.app')

@section('content')
    <section class="hero-bg px-4 sm:px-6 lg:px-8 pt-10 pb-20">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white text-center mb-8">{{ __('ui.car_hero_title') }}</h1>

            <div class="glass-card rounded-3xl p-4 sm:p-6 shadow-2xl border border-white/60">
                @include('partials.car-search-form')
            </div>
        </div>
    </section>
@endsection