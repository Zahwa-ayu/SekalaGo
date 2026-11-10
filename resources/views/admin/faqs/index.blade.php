@extends('layouts.dashboard')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-black text-slate-900">Kelola FAQ</h1>
        <a href="{{ route('admin.faqs.create') }}" class="px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl text-sm">
            + Tambah FAQ
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="text-left px-4 py-3">Urutan</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Pertanyaan (ID)</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faqs as $faq)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $faq->sort_order }}</td>
                        <td class="px-4 py-3">{{ $faq->category }}</td>
                        <td class="px-4 py-3">{{ $faq->translations->firstWhere('locale', 'id')->question ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $faq->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $faq->is_active ? 'Aktif' : 'Tersembunyi' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-sky-600 font-bold text-xs hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="inline"
                                  onsubmit="return confirm('Hapus FAQ ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 font-bold text-xs hover:underline ml-3">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada FAQ.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection