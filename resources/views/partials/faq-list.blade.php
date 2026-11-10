<div class="space-y-3">
    @forelse ($items as $faq)
        @php $t = $faq->translation(); @endphp

        @if ($t)
            <details class="rounded-2xl bg-white border border-slate-200/80 px-5 py-4 group shadow-sm transition duration-200 hover:border-sky-300">
                <summary class="font-bold text-sm cursor-pointer list-none flex justify-between items-center text-slate-900 select-none">
                    <span>{{ $t->question }}</span>
                    <span class="w-6 h-6 rounded-full bg-slate-100 group-open:bg-sky-50 group-open:text-sky-600 flex items-center justify-center text-slate-400 transition-all duration-200 shrink-0 ml-2">
                        <i class="fa-solid fa-chevron-down text-[10px] group-open:rotate-180 transition-transform"></i>
                    </span>
                </summary>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed border-t border-slate-100 pt-3 whitespace-pre-line">
                    {{ $t->answer }}
                </p>
            </details>
        @endif
    @empty
        <p class="text-xs text-slate-400 italic">Belum ada pertanyaan umum.</p>
    @endforelse
</div>