@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-2 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.facilities') }}</h1>
        <button type="button" onclick="openModal('create')"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.add_facility') }}
        </button>
    </div>
    <p class="text-sm text-slate-500 mb-6">{{ __('ui.facilities_hint') }}</p>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <form method="GET" action="{{ route('admin.facilities.index') }}" class="px-6 py-4 border-b border-slate-100 flex flex-wrap gap-2 items-center">
            <input name="q" value="{{ $search }}" placeholder="{{ __('ui.facility_search') }}"
                   class="flex-1 min-w-48 rounded-xl border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30">
            <select name="scope" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">{{ __('ui.facility_all_scopes') }}</option>
                @foreach (['hotel', 'room', 'both'] as $value)
                    <option value="{{ $value }}" @selected($scope === $value)>{{ __('ui.scope_' . $value) }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">{{ __('ui.search') }}</button>
            @if ($search !== '' || $scope)
                <a href="{{ route('admin.facilities.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">{{ __('ui.reset') }}</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-bold">{{ __('ui.facilities') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.facility_scope') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('ui.facility_used') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.order') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($facilities as $facility)
                        @php
                            $used = $facility->hotels_count + $facility->room_types_count;
                            $payload = [
                                'id'         => $facility->id,
                                'code'       => $facility->code,
                                'names'      => collect($locales)->keys()->mapWithKeys(fn ($l) => [$l => $facility->nameIn($l) ?? ''])->all(),
                                'scope'      => $facility->scope,
                                'icon_class' => $facility->icon(),
                                'sort_order' => $facility->sort_order,
                                'active'     => $facility->is_active,
                            ];
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sky-100 text-sagara-blue flex items-center justify-center text-sm shrink-0">
                                        <i class="fa-solid {{ $facility->icon() }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $facility->label }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $facility->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ __('ui.scope_' . $facility->scope) }}</td>
                            <td class="px-6 py-4 text-right text-xs text-slate-600">
                                {{ __('ui.facility_used_hotels', ['count' => $facility->hotels_count]) }}<br>
                                {{ __('ui.facility_used_rooms', ['count' => $facility->room_types_count]) }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $facility->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $facility->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $facility->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-sagara-blue hover:underline"
                                            data-facility="{{ json_encode($payload) }}"
                                            onclick="openModal('edit', JSON.parse(this.dataset.facility))">
                                        {{ __('ui.edit') }}
                                    </button>

                                    <form method="POST" action="{{ route('admin.facilities.status', $facility) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-amber-600 hover:underline">{{ $facility->is_active ? __('ui.deactivate') : __('ui.activate') }}</button>
                                    </form>

                                    @if ($used === 0)
                                        <form method="POST" action="{{ route('admin.facilities.destroy', $facility) }}"
                                              data-confirm="{{ __('ui.confirm_delete', ['name' => $facility->label]) }}"
                                              onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-list-check text-3xl mb-2 block"></i>
                                {{ __('ui.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">{{ $facilities->links() }}</div>
    </div>
</section>

{{-- ============ MODAL (tambah dan ubah) ============ --}}
<div id="fModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-sagara-dark/60 backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="relative bg-white rounded-3xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex items-center justify-between mb-5">
            <h2 id="fTitle" class="text-xl font-extrabold text-slate-900">{{ __('ui.add_facility') }}</h2>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form id="fForm" method="POST" action="{{ route('admin.facilities.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <input type="hidden" name="_method" id="fMethod" value="PUT" disabled>
            <input type="hidden" name="_mode" id="fMode" value="{{ old('_mode', 'create') }}">
            <input type="hidden" name="_id" id="fId" value="{{ old('_id') }}">

            <div class="md:col-span-2 grid md:grid-cols-[100px_1fr] gap-4 items-start">
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-1">{{ __('ui.facility_icon') }}</label>
                    <div class="w-14 h-14 rounded-full bg-sky-100 text-sagara-blue flex items-center justify-center text-xl">
                        <i id="fPreview" class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('ui.facility_icon_class') }}</label>
                    <input type="text" name="icon_class" id="fIcon" required maxlength="60" placeholder="fa-wifi"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 font-mono focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                    <p class="text-xs text-slate-500 mt-1">{{ __('ui.facility_icon_hint') }}</p>

                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach ($presets as $preset)
                            <button type="button" data-icon="{{ $preset }}" title="{{ $preset }}"
                                    class="icon-choice w-9 h-9 rounded-lg border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sagara-blue transition">
                                <i class="fa-solid {{ $preset }}"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.facility_names') }}</label>
                <p class="text-xs text-slate-500 mt-1 mb-2">{{ __('ui.facility_names_hint') }}</p>

                <div class="space-y-3">
                    @foreach ($locales as $code => $name)
                        <div data-name-row="{{ $code }}" class="{{ in_array($code, ['id', 'en'], true) ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    {{ $name }}
                                    @if (in_array($code, ['id', 'en'], true))
                                        <span class="text-red-600">*</span>
                                    @else
                                        <span class="normal-case font-semibold">(opsional)</span>
                                    @endif
                                </span>
                                @if (! in_array($code, ['id', 'en'], true))
                                    <button type="button" data-remove-name="{{ $code }}" class="text-xs font-bold text-slate-400 hover:text-red-600 transition">
                                        <i class="fa-solid fa-xmark"></i> Tutup
                                    </button>
                                @endif
                            </div>
                            <input type="text" name="names[{{ $code }}]" id="fName_{{ $code }}" maxlength="60" @required(in_array($code, ['id', 'en'], true))
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                        </div>
                    @endforeach
                </div>

                <div id="fLangPicker" class="mt-3">
                    <button type="button" id="fLangToggle"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border-2 border-dashed border-slate-200 text-sm font-bold text-slate-500 hover:border-sagara-blue hover:text-sagara-blue transition">
                        <i class="fa-solid fa-language"></i> Tambahkan bahasa lainnya
                    </button>
                    <div id="fLangMenu" class="hidden mt-3 flex flex-wrap gap-2">
                        @foreach ($locales as $code => $name)
                            @if (! in_array($code, ['id', 'en'], true))
                                <button type="button" data-add-name="{{ $code }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                                    <i class="fa-solid fa-plus"></i> {{ $name }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.facility_scope') }}</label>
                <select name="scope" id="fScope" required
                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                    @foreach (['hotel', 'room', 'both'] as $value)
                        <option value="{{ $value }}">{{ __('ui.scope_' . $value) }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">{{ __('ui.facility_scope_hint') }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.order') }}</label>
                <input type="number" name="sort_order" id="fOrder" min="0" max="9999" value="0"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>

            <div id="fCodeRow" class="hidden md:col-span-2 text-xs text-slate-500">
                {{ __('ui.facility_code') }}: <span id="fCode" class="font-mono font-bold text-slate-700"></span>
            </div>

            <div class="flex items-center">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" id="fActive" value="1" checked class="rounded border-slate-300 text-sagara-blue">
                    {{ __('ui.active') }}
                </label>
            </div>

            <div class="md:col-span-2 flex gap-3 mt-2">
                <button id="fSubmit" class="px-6 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition">
                    {{ __('ui.add_facility') }}
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
    const storeUrl  = @json(route('admin.facilities.store'));
    const updateUrl = @json(route('admin.facilities.update', '__ID__'));
    const T = {
        addTitle:  @json(__('ui.add_facility')),
        editTitle: @json(__('ui.edit_facility')),
        updateBtn: @json(__('ui.update_facility')),
    };

    const $ = (id) => document.getElementById(id);
    const modal = $('fModal'), form = $('fForm');
    const LOCALES = @json(array_keys($locales));
    const REQUIRED = ['id', 'en'];
    const nameRow = (c) => document.querySelector('[data-name-row="' + c + '"]');
    const addBtn  = (c) => document.querySelector('[data-add-name="' + c + '"]');

    // Tombol "Tambahkan bahasa lainnya" hanya tampil kalau masih ada bahasa yang tersembunyi
    function syncLangPicker() {
        let remaining = false;
        LOCALES.forEach((c) => {
            if (REQUIRED.includes(c)) return;
            const hidden = nameRow(c).classList.contains('hidden');
            addBtn(c).classList.toggle('hidden', !hidden);
            if (hidden) remaining = true;
        });
        $('fLangPicker').classList.toggle('hidden', !remaining);
        if (!remaining) $('fLangMenu').classList.add('hidden');
    }

    // Indonesia dan English selalu tampil; bahasa lain tampil hanya kalau sudah ada isinya
    function setNames(names) {
        LOCALES.forEach((c) => {
            const v = (names && names[c]) || '';
            $('fName_' + c).value = v;
            if (!REQUIRED.includes(c)) nameRow(c).classList.toggle('hidden', v === '');
        });
        $('fLangMenu').classList.add('hidden');
        syncLangPicker();
    }

    function refreshPreview() {
        const value = $('fIcon').value.trim();
        $('fPreview').className = 'fa-solid ' + (/^fa-[a-z0-9-]+$/.test(value) ? value : 'fa-circle-question');
    }

    function openModal(mode, d = null) {
        const edit = mode === 'edit';
        $('fMode').value = mode;
        $('fTitle').textContent  = edit ? T.editTitle : T.addTitle;
        $('fSubmit').textContent = edit ? T.updateBtn : T.addTitle;
        form.action = edit ? updateUrl.replace('__ID__', d.id) : storeUrl;
        $('fMethod').disabled = !edit;
        $('fId').value = edit ? d.id : '';
        $('fCodeRow').classList.toggle('hidden', !edit);

        if (edit) {
            $('fCode').textContent = d.code;
            setNames(d.names);
            $('fScope').value = d.scope;
            $('fIcon').value = d.icon_class;
            $('fOrder').value = d.sort_order;
            $('fActive').checked = d.active;
        } else {
            form.reset();
            setNames({});
            $('fIcon').value = 'fa-circle-check';
        }

        refreshPreview();
        modal.classList.remove('hidden');
    }

    function closeModal() { modal.classList.add('hidden'); }

    $('fIcon').addEventListener('input', refreshPreview);
    document.querySelectorAll('.icon-choice').forEach((button) => {
        button.addEventListener('click', () => { $('fIcon').value = button.dataset.icon; refreshPreview(); });
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    $('fLangToggle').addEventListener('click', () => $('fLangMenu').classList.toggle('hidden'));

    document.querySelectorAll('[data-add-name]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const code = btn.dataset.addName;
            nameRow(code).classList.remove('hidden');
            $('fName_' + code).focus();
            syncLangPicker();
        });
    });

    document.querySelectorAll('[data-remove-name]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const code = btn.dataset.removeName;
            $('fName_' + code).value = '';
            nameRow(code).classList.add('hidden');
            syncLangPicker();
        });
    });

    // Validasi gagal: buka lagi modal dengan isian sebelumnya
    @if ($errors->any())
        (function () {
            const mode = @json(old('_mode', 'create'));
            openModal(mode, mode === 'edit'
                ? { id: @json(old('_id')), code: '', names: {}, scope: 'both', icon_class: '', sort_order: 0, active: false }
                : null);
            setNames(@json(old('names', [])));
            $('fScope').value = @json(old('scope', 'both'));
            $('fIcon').value = @json(old('icon_class', ''));
            $('fOrder').value = @json(old('sort_order', 0));
            $('fActive').checked = @json((bool) old('is_active'));
            refreshPreview();
        })();
    @endif
</script>
@endpush
@endsection