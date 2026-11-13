@extends('layouts.app')

@section('content')
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> Kelola FAQ
        </a>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Tambah FAQ</h1>

        <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-6">
            @csrf
            @include('admin.faqs._form', ['submitLabel' => 'Simpan'])
        </form>
    </section>
@endsection