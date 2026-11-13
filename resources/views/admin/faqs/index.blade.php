@extends('layouts.app')

@section('content')
    @php
        $th   = 'px-6 py-3 font-bold';
        $td   = 'px-6 py-3';
        $chip = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap';
    @endphp

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- HEADER --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Kelola FAQ</h1>
            <a href="{{ route('admin.faqs.create') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                <i class="fa-solid fa-plus"></i> Tambah FAQ
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif

        {{-- TABEL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="{{ $th }}">Urutan</th>
                            <th class="{{ $th }}">Kategori</th>
                            <th class="{{ $th }}">Pertanyaan (ID)</th>
                            <th class="{{ $th }}">Status</th>
                            <th class="{{ $th }}"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($faqs as $faq)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="{{ $td }} text-slate-600">{{ $faq->sort_order }}</td>
                                <td class="{{ $td }}"><span class="{{ $chip }} bg-slate-100 text-slate-700">{{ $faq->category }}</span></td>
                                <td class="{{ $td }} min-w-64 font-bold text-slate-900">{{ $faq->translations->firstWhere('locale', 'id')->question ?? '-' }}</td>
                                <td class="{{ $td }}">
                                    <span class="{{ $chip }} {{ $faq->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $faq->is_active ? 'Aktif' : 'Tersembunyi' }}
                                    </span>
                                </td>
                                <td class="{{ $td }} text-right whitespace-nowrap">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold border border-sagara-blue text-sagara-blue hover:bg-sagara-blue hover:text-white rounded-xl transition whitespace-nowrap">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="inline ml-1"
                                          onsubmit="return confirm('Hapus FAQ ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold border border-red-600 text-red-600 hover:bg-red-600 hover:text-white rounded-xl transition whitespace-nowrap">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-regular fa-circle-question text-3xl mb-2 block"></i>
                                    Belum ada FAQ.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection