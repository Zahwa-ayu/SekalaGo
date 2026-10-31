@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.social_media') }}</h1>
        <button type="button" onclick="openModal('create')"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.add_social_media') }}
        </button>
    </div>

    @if (session('success'))
        <div class="px-4 py-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-bold">{{ __('ui.platform') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.url') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.order') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($socialMedia as $sm)
                        @php
                            $isActive = $sm->status === 'aktif';
                            $payload = [
                                'id'              => $sm->id,
                                'platform_choice' => array_key_exists($sm->platform, $platforms) ? $sm->platform : 'Lainnya (Kustom)',
                                'platform'        => $sm->platform,
                                'icon_class'      => $sm->icon_class,
                                'color'           => $sm->color,
                                'url'             => $sm->url,
                                'order'           => $sm->order,
                                'active'          => $isActive,
                            ];
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm shrink-0"
                                         style="background-color: {{ $sm->color ?? '#999' }}">
                                        <i class="{{ $sm->icon_class }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $sm->platform }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $sm->icon_class }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ $sm->url }}" target="_blank" rel="noopener"
                                   class="text-sagara-blue hover:underline break-all">{{ $sm->url }}</a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $sm->order }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $isActive ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-sagara-blue hover:underline"
                                            data-sm="{{ json_encode($payload) }}"
                                            onclick="openModal('edit', JSON.parse(this.dataset.sm))">
                                        {{ __('ui.edit') }}
                                    </button>

                                    <form method="POST" action="{{ route('admin.social-media.status', $sm) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-amber-600 hover:underline">
                                            {{ $isActive ? __('ui.deactivate') : __('ui.activate') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.social-media.destroy', $sm) }}"
                                          data-confirm="{{ __('ui.confirm_delete', ['name' => $sm->platform]) }}"
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
                                <i class="fa-solid fa-share-nodes text-3xl mb-2 block"></i>
                                {{ __('ui.no_social_media') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $socialMedia->links() }}
        </div>
    </div>
</section>

{{-- ============ MODAL (tambah & edit) ============ --}}
<div id="smModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-sagara-dark/60 backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="relative bg-white rounded-3xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex items-center justify-between mb-5">
            <h2 id="smTitle" class="text-xl font-extrabold text-slate-900">{{ __('ui.add_social_media') }}</h2>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form id="smForm" method="POST" action="{{ route('admin.social-media.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <input type="hidden" name="_method" id="smMethod" value="PUT" disabled>
            <input type="hidden" name="_mode" id="smMode" value="{{ old('_mode', 'create') }}">
            <input type="hidden" name="_id" id="smId" value="{{ old('_id') }}">

            <div class="md:col-span-2 grid md:grid-cols-[100px_1fr] gap-4 items-start">
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-1">{{ __('ui.logo_color') }}</label>
                    <div id="previewIcon" class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl" style="background-color:#999">
                        <i id="previewIconTag" class="fas fa-link"></i>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('ui.choose_platform') }}</label>
                    <select name="platform_choice" id="platformSelect" required
                            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                        <option value="">{{ __('ui.choose_platform_ph') }}</option>
                        @foreach ($platforms as $name => $meta)
                            <option value="{{ $name }}" data-icon="{{ $meta['icon'] }}" data-color="{{ $meta['color'] }}">{{ $name }}</option>
                        @endforeach
                        <option value="Lainnya (Kustom)">{{ __('ui.custom_platform') }}</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">{{ __('ui.choose_platform_hint') }}</p>
                </div>
            </div>

            <div id="customFields" class="hidden md:col-span-2 grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('ui.platform_name') }}</label>
                    <input type="text" name="custom_platform_name" id="fName" placeholder="SekalaGo Blog"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">{{ __('ui.icon') }}</label>
                    <input type="text" name="custom_icon_class" id="fIcon" placeholder="fab fa-facebook"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                    <p class="text-xs text-slate-500 mt-1">{{ __('ui.icon_hint') }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">{{ __('ui.brand_color') }}</label>
                    <div class="flex items-center gap-3 mt-1">
                        <input type="color" id="colorPicker" value="#000000"
                               class="w-12 h-10 border border-slate-300 rounded-lg cursor-pointer p-1">
                        <input type="text" name="custom_color" id="colorText" value="#000000" placeholder="#000000"
                               class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.link_url') }}</label>
                <input type="url" name="url" id="fUrl" required placeholder="https://instagram.com/username"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.order') }}</label>
                <input type="number" name="order" id="fOrder" min="0" required value="0"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>
            <div class="flex items-center">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="status" id="fStatus" value="1" checked class="rounded border-slate-300 text-sagara-blue">
                    {{ __('ui.active') }}
                </label>
            </div>

            <div class="md:col-span-2 flex gap-3 mt-2">
                <button id="smSubmit" class="px-6 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition">
                    {{ __('ui.add_social_media') }}
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
    const CUSTOM = 'Lainnya (Kustom)';
    const storeUrl  = @json(route('admin.social-media.store'));
    const updateUrl = @json(route('admin.social-media.update', '__ID__'));
    const T = {
        addTitle:  @json(__('ui.add_social_media')),
        editTitle: @json(__('ui.edit_social_media')),
        updateBtn: @json(__('ui.update_social_media')),
    };

    const $ = id => document.getElementById(id);
    const modal = $('smModal'), form = $('smForm'), select = $('platformSelect');

    function refreshPreview() {
        const opt = select.options[select.selectedIndex];
        const isCustom = select.value === CUSTOM;
        $('customFields').classList.toggle('hidden', !isCustom);

        if (isCustom) {
            $('previewIconTag').className = $('fIcon').value || 'fas fa-link';
            $('previewIcon').style.backgroundColor = $('colorText').value || '#999';
        } else if (opt && opt.dataset.icon) {
            $('previewIconTag').className = opt.dataset.icon;
            $('previewIcon').style.backgroundColor = opt.dataset.color || '#999';
        } else {
            $('previewIconTag').className = 'fas fa-link';
            $('previewIcon').style.backgroundColor = '#999';
        }
    }

    function openModal(mode, d = null) {
        const edit = mode === 'edit';
        $('smMode').value = mode;
        $('smTitle').textContent  = edit ? T.editTitle : T.addTitle;
        $('smSubmit').textContent = edit ? T.updateBtn : T.addTitle;
        form.action = edit ? updateUrl.replace('__ID__', d.id) : storeUrl;
        $('smMethod').disabled = !edit;
        $('smId').value = edit ? d.id : '';

        if (edit) {
            select.value = d.platform_choice;
            const custom = d.platform_choice === CUSTOM;
            $('fName').value = custom ? d.platform : '';
            $('fIcon').value = custom ? d.icon_class : '';
            $('colorText').value = $('colorPicker').value = (custom && d.color) ? d.color : '#000000';
            $('fUrl').value = d.url;
            $('fOrder').value = d.order;
            $('fStatus').checked = d.active;
        } else {
            form.reset();
            $('colorText').value = $('colorPicker').value = '#000000';
        }
        refreshPreview();
        modal.classList.remove('hidden');
    }

    function closeModal() { modal.classList.add('hidden'); }

    select.addEventListener('change', refreshPreview);
    $('fIcon').addEventListener('input', refreshPreview);
    $('colorPicker').addEventListener('input', e => { $('colorText').value = e.target.value; refreshPreview(); });
    $('colorText').addEventListener('input', e => {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) $('colorPicker').value = e.target.value;
        refreshPreview();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Validasi gagal -> buka lagi modal dengan input sebelumnya
    @if ($errors->any())
        (function () {
            const mode = @json(old('_mode', 'create'));
            openModal(mode, mode === 'edit'
                ? { id: @json(old('_id')), platform_choice: '', platform: '', icon_class: '', color: null, url: '', order: 0, active: false }
                : null);
            select.value = @json(old('platform_choice', ''));
            $('fName').value = @json(old('custom_platform_name', ''));
            $('fIcon').value = @json(old('custom_icon_class', ''));
            $('colorText').value = $('colorPicker').value = @json(old('custom_color', '#000000')) || '#000000';
            $('fUrl').value = @json(old('url', ''));
            $('fOrder').value = @json(old('order', 0));
            $('fStatus').checked = @json((bool) old('status'));
            refreshPreview();
        })();
    @endif
</script>
@endpush
@endsection