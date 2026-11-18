@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-2 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.contact_us') }}</h1>
        <button type="button" onclick="openModal('create')"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Kontak
        </button>
    </div>
    <p class="text-sm text-slate-500 mb-6">Tampil di footer website, urut sesuai kolom Urutan. Sebaiknya cukup 4-5 kontak aktif supaya footer tetap ringkas.</p>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-bold">Tipe</th>
                        <th class="px-6 py-4 font-bold">Isi</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.order') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contacts as $contact)
                        @php
                            $payload = [
                                'id'     => $contact->id,
                                'type'   => $contact->type,
                                'label'  => $contact->label,
                                'value'  => $contact->value,
                                'order'  => $contact->order,
                                'active' => $contact->is_active,
                            ];
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sky-100 text-sagara-blue flex items-center justify-center text-sm shrink-0">
                                        <i class="{{ $contact->icon }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $contact->type_name }}</div>
                                        @if ($contact->label)
                                            <div class="text-[11px] text-slate-500">{{ $contact->label }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700 break-words max-w-xs">{{ $contact->value }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $contact->order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $contact->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $contact->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-sagara-blue hover:underline"
                                            data-contact="{{ json_encode($payload) }}"
                                            onclick="openModal('edit', JSON.parse(this.dataset.contact))">
                                        {{ __('ui.edit') }}
                                    </button>

                                    <form method="POST" action="{{ route('admin.contact-info.status', $contact) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-amber-600 hover:underline">{{ $contact->is_active ? __('ui.deactivate') : __('ui.activate') }}</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.contact-info.destroy', $contact) }}"
                                          data-confirm="{{ __('ui.confirm_delete', ['name' => $contact->type_name]) }}"
                                          onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-address-book text-3xl mb-2 block"></i>
                                Belum ada kontak.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ============ MODAL (tambah dan ubah) ============ --}}
<div id="cModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-sagara-dark/60 backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="relative bg-white rounded-3xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex items-center justify-between mb-5">
            <h2 id="cTitle" class="text-xl font-extrabold text-slate-900">Tambah Kontak</h2>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form id="cForm" method="POST" action="{{ route('admin.contact-info.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <input type="hidden" name="_method" id="cMethod" value="PUT" disabled>
            <input type="hidden" name="_mode" id="cMode" value="{{ old('_mode', 'create') }}">
            <input type="hidden" name="_id" id="cId" value="{{ old('_id') }}">

            <div class="md:col-span-2 grid md:grid-cols-[100px_1fr] gap-4 items-start">
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-1">Ikon</label>
                    <div class="w-14 h-14 rounded-full bg-sky-100 text-sagara-blue flex items-center justify-center text-xl">
                        <i id="cPreviewTag" class="fa-solid fa-circle-info"></i>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Tipe kontak <span class="text-red-600">*</span></label>
                    <select name="type" id="cType" required
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                        <option value="">Pilih tipe</option>
                        @foreach ($types as $key => $meta)
                            <option value="{{ $key }}" data-icon="{{ $meta['icon'] }}" data-placeholder="{{ $meta['placeholder'] ?? '' }}">{{ $meta['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Isi <span class="text-red-600">*</span></label>
                <input type="text" name="value" id="cValue" required maxlength="255"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Keterangan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input type="text" name="label" id="cLabel" maxlength="100" placeholder="Customer Service"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                <p class="text-xs text-slate-500 mt-1">Teks kecil di atas isi kontak di footer. Tidak diterjemahkan, jadi tulis yang netral.</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.order') }}</label>
                <input type="number" name="order" id="cOrder" min="0" required value="0"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" id="cActive" value="1" checked class="rounded border-slate-300 text-sagara-blue">
                    {{ __('ui.active') }}
                </label>
            </div>

            <div class="md:col-span-2 flex gap-3 mt-2">
                <button id="cSubmit" class="px-6 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition">
                    Tambah Kontak
                </button>
                <button type="button" onclick="closeModal()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 border border-slate-300 hover:bg-slate-50 rounded-xl transition">
                    {{ __('ui.cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const storeUrl  = @json(route('admin.contact-info.store'));
    const updateUrl = @json(route('admin.contact-info.update', '__ID__'));
    const T = { addTitle: 'Tambah Kontak', editTitle: 'Ubah Kontak', updateBtn: 'Perbarui' };

    const $ = (id) => document.getElementById(id);
    const modal = $('cModal'), form = $('cForm'), typeSel = $('cType');

    // Ikon dan contoh isi mengikuti tipe yang dipilih
    function refreshType() {
        const opt = typeSel.options[typeSel.selectedIndex];
        $('cPreviewTag').className = (opt && opt.dataset.icon) || 'fa-solid fa-circle-info';
        $('cValue').placeholder = (opt && opt.dataset.placeholder) || '';
        $('cValue').type = typeSel.value === 'email' ? 'email' : 'text';
    }

    function openModal(mode, d = null) {
        const edit = mode === 'edit';
        $('cMode').value = mode;
        $('cTitle').textContent  = edit ? T.editTitle : T.addTitle;
        $('cSubmit').textContent = edit ? T.updateBtn : T.addTitle;
        form.action = edit ? updateUrl.replace('__ID__', d.id) : storeUrl;
        $('cMethod').disabled = !edit;
        $('cId').value = edit ? d.id : '';

        if (edit) {
            typeSel.value = d.type;
            $('cValue').value = d.value || '';
            $('cLabel').value = d.label || '';
            $('cOrder').value = d.order;
            $('cActive').checked = d.active;
        } else {
            form.reset();
        }

        refreshType();
        modal.classList.remove('hidden');
    }

    function closeModal() { modal.classList.add('hidden'); }

    typeSel.addEventListener('change', refreshType);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    // Validasi gagal: buka lagi modal dengan isian sebelumnya
    @if ($errors->any())
        (function () {
            const mode = @json(old('_mode', 'create'));
            openModal(mode, mode === 'edit'
                ? { id: @json(old('_id')), type: '', value: '', label: '', order: 0, active: false }
                : null);
            typeSel.value = @json(old('type', ''));
            $('cValue').value = @json(old('value', ''));
            $('cLabel').value = @json(old('label', ''));
            $('cOrder').value = @json(old('order', 0));
            $('cActive').checked = @json((bool) old('is_active'));
            refreshType();
        })();
    @endif
</script>
@endpush
@endsection