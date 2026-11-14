@extends('layouts.app')

@section('content')
    @php
        $th      = 'px-6 py-4 font-bold';
        $td      = 'px-6 py-4';
        $chip    = 'px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap';
    @endphp

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Kelola FAQ</h1>
        <a href="{{ route('admin.faqs.create') }}"
           class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> Tambah FAQ
        </a>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
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
                        <th class="{{ $th }}">{{ __('ui.status') }}</th>
                        <th class="{{ $th }}">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($faqs as $faq)
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="{{ $td }} text-slate-600">{{ $faq->sort_order }}</td>
                            <td class="{{ $td }}"><span class="{{ $chip }} bg-slate-100 text-slate-600">{{ $faq->category }}</span></td>
                            <td class="{{ $td }} min-w-64 font-bold text-slate-900">{{ $faq->translations->firstWhere('locale', 'id')->question ?? '-' }}</td>
                            <td class="{{ $td }}">
                                <span class="{{ $chip }} {{ $faq->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $faq->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="{{ $td }} whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}"
                                          onsubmit="return confirm('Hapus FAQ ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                    </form>
                                </div>
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