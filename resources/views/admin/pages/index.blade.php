@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-2 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.pages') }}</h1>
        <a href="{{ route('admin.pages.create') }}"
           class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Halaman
        </a>
    </div>
    <p class="text-sm text-slate-500 mb-6">Halaman statis seperti Tentang Kami, Syarat &amp; Ketentuan, dan Kebijakan Privasi. Halaman yang dicentang "tampil di footer" muncul sebagai tautan di bagian bawah website.</p>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-bold">Halaman</th>
                        <th class="px-6 py-4 font-bold">Bahasa</th>
                        <th class="px-6 py-4 font-bold">Footer</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.order') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pages as $page)
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">
                                    {{ $page->title }}
                                    @if ($page->template === 'about')
                                        <span class="ml-1.5 align-middle px-2 py-0.5 text-[10px] font-bold rounded bg-sky-100 text-sky-700">Berbagian</span>
                                    @endif
                                </div>
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" rel="noopener"
                                   class="text-[11px] text-sagara-blue hover:underline font-mono">/page/{{ $page->slug }}</a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($page->translations->sortBy('locale') as $translation)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-600">{{ strtoupper($translation->locale) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @if ($page->show_in_footer)
                                    <i class="fa-solid fa-check text-emerald-600"></i>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $page->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $page->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $page->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-sagara-blue hover:underline">{{ __('ui.edit') }}</a>

                                    @include('partials.delete', [
                                        'action'  => route('admin.pages.destroy', $page),
                                        'title'   => 'Hapus halaman ini?',
                                        'message' => '"' . $page->title . '" dan semua terjemahannya akan dihapus.',
                                        'label'   => __('ui.delete'),
                                    ])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-lines text-3xl mb-2 block"></i>
                                Belum ada halaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection