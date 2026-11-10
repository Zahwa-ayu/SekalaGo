@extends('layouts.dashboard')

@section('content')
    <div class="max-w-3xl">
        <h1 class="text-xl font-black text-slate-900 mb-6">Edit FAQ</h1>
        <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
            @csrf
            @method('PUT')
            @include('admin.faqs._form')
            <div class="flex gap-3">
                <button class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl text-sm">Perbarui</button>
                <a href="{{ route('admin.faqs.index') }}" class="px-5 py-2.5 text-slate-600 text-sm font-bold">Batal</a>
            </div>
        </form>
    </div>
@endsection 