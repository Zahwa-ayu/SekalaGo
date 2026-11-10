@php
    $faq = $faq ?? null;
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs list-disc list-inside mb-4">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div>
        <label class="block text-xs font-bold text-slate-500 mb-1">Kategori</label>
        <select name="category" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm">
            @foreach (['partner' => 'Partner', 'customer' => 'Customer', 'corporate' => 'Corporate'] as $val => $label)
                <option value="{{ $val }}" @selected(old('category', $faq->category ?? 'partner') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-slate-500 mb-1">Urutan</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $faq->sort_order ?? 0) }}"
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))>
            Tampilkan di website
        </label>
    </div>
</div>

@foreach ($locales as $code => $name)
    @php $tr = $faq?->translations->firstWhere('locale', $code); @endphp
    <div class="rounded-2xl border border-slate-200 p-4 mb-4">
        <h3 class="text-sm font-black text-slate-800 mb-3">
            {{ $name }} ({{ strtoupper($code) }})
            @if ($code === 'id') <span class="text-red-500 text-xs">wajib</span> @else <span class="text-slate-400 text-xs">opsional</span> @endif
        </h3>

        <label class="block text-xs font-bold text-slate-500 mb-1">Pertanyaan</label>
        <input type="text" name="question[{{ $code }}]" value="{{ old("question.$code", $tr->question ?? '') }}"
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm mb-3">

        <label class="block text-xs font-bold text-slate-500 mb-1">Jawaban</label>
        <textarea name="answer[{{ $code }}]" rows="3"
                  class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm">{{ old("answer.$code", $tr->answer ?? '') }}</textarea>
    </div>
@endforeach