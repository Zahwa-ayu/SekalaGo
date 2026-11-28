@extends('layouts.app')

@section('content')
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('corporate.employees.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.employees') }}
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.edit_employee') }}</h1>

        <form method="POST" action="{{ route('corporate.employees.update', $employee) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-5">
                @include('corporate.employees._form')
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    <i class="fa-solid fa-check"></i> {{ __('ui.save') }}
                </button>
                <a href="{{ route('corporate.employees.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </section>
@endsection