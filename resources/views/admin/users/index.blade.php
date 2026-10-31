@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.um_title') }}</h1>
        <button type="button" onclick="openUserModal('create')"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
            <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.um_add') }}
        </button>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.um_search_ph') }}"
               class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
        <select name="type"
                class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            <option value="">{{ __('ui.um_all_roles') }}</option>
            @foreach ($allTypes as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ __('ui.um_role_' . $t) }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2.5 text-sm font-semibold text-slate-700 border border-slate-300 hover:bg-slate-50 rounded-xl transition">
            {{ __('ui.um_filter') }}
        </button>
    </form>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-bold">{{ __('ui.um_name') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.um_email') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.um_role') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.um_joined') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $u)
                        @php
                            $isSelf = $u->is(auth()->user());
                            $locked = $isSelf || in_array($u->user_type, $lockedTypes, true);
                            $badge = match ($u->user_type) {
                                'admin'     => 'bg-sagara-navy/10 text-sagara-navy',
                                'partner'   => 'bg-amber-100 text-amber-700',
                                'corporate' => 'bg-violet-100 text-violet-700',
                                default     => 'bg-sky-100 text-sky-700',
                            };
                            $payload = [
                                'id'           => $u->id,
                                'full_name'    => $u->display_name === $u->email ? '' : $u->display_name,
                                'email'        => $u->email,
                                'phone_number' => $u->phone_number,
                                'user_type'    => $u->user_type,
                                'locked'       => $locked,
                            ];
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sagara-blue/10 text-sagara-blue flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(mb_substr($u->display_name, 0, 1)) }}
                                    </div>
                                    <div class="font-bold text-slate-900">
                                        {{ $u->display_name }}
                                        @if ($isSelf)
                                            <span class="ml-1 text-[10px] font-bold text-sagara-teal uppercase">({{ __('ui.um_you') }})</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 break-all">{{ $u->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $badge }}">
                                    {{ __('ui.um_role_' . $u->user_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $u->created_at?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $u->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-sagara-blue hover:underline"
                                            data-user="{{ json_encode($payload) }}"
                                            onclick="openUserModal('edit', JSON.parse(this.dataset.user))">
                                        {{ __('ui.edit') }}
                                    </button>

                                    @unless ($isSelf)
                                        <form method="POST" action="{{ route('admin.users.status', $u) }}">
                                            @csrf @method('PATCH')
                                            <button class="text-amber-600 hover:underline">
                                                {{ $u->is_active ? __('ui.um_deactivate') : __('ui.um_activate') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                              data-confirm="{{ __('ui.confirm_delete', ['name' => $u->display_name]) }}"
                                              onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:underline">{{ __('ui.delete') }}</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2 block"></i>
                                {{ __('ui.um_empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</section>

{{-- ============ MODAL (tambah & edit) ============ --}}
<div id="userModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-sagara-dark/60 backdrop-blur-sm" onclick="closeUserModal()"></div>

    <div class="relative bg-white rounded-3xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex items-center justify-between mb-5">
            <h2 id="userTitle" class="text-xl font-extrabold text-slate-900">{{ __('ui.um_add') }}</h2>
            <button type="button" onclick="closeUserModal()" class="text-slate-400 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="userMethod" value="PUT" disabled>
            <input type="hidden" name="_mode" id="userMode" value="{{ old('_mode', 'create') }}">
            <input type="hidden" name="_id" id="userId" value="{{ old('_id') }}">

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.um_name') }}</label>
                <input type="text" name="full_name" id="uName" required
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.um_email') }}</label>
                <input type="email" name="email" id="uEmail" required
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.um_phone') }}</label>
                <input type="text" name="phone_number" id="uPhone" maxlength="20"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.um_role') }}</label>
                <select name="user_type" id="uType"
                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none disabled:bg-slate-100 disabled:text-slate-500">
                    @foreach ($manageable as $t)
                        <option value="{{ $t }}">{{ __('ui.um_role_' . $t) }}</option>
                    @endforeach
                    {{-- Hanya muncul saat edit akun partner/corporate (role terkunci) --}}
                    @foreach ($lockedTypes as $t)
                        <option value="{{ $t }}" hidden>{{ __('ui.um_role_' . $t) }}</option>
                    @endforeach
                </select>
                <p id="uTypeHint" class="hidden text-xs text-slate-500 mt-1">{{ __('ui.um_role_locked_hint') }}</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">{{ __('ui.um_password') }}</label>
                <input type="password" name="password" id="uPassword" autocomplete="new-password"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2.5 mt-1 text-slate-900 focus:border-sagara-teal focus:ring-2 focus:ring-sagara-teal/30 outline-none">
                <p id="uPasswordHint" class="hidden text-xs text-slate-500 mt-1">{{ __('ui.um_password_edit_hint') }}</p>
            </div>

            {{-- Hanya untuk tambah; saat edit status diubah lewat tombol di tabel --}}
            <label id="uActiveWrap" class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" id="uActive" value="1" checked class="rounded border-slate-300 text-sagara-blue">
                {{ __('ui.active') }}
            </label>

            <div class="flex gap-3 pt-2">
                <button id="userSubmit" class="px-6 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition">
                    {{ __('ui.um_add') }}
                </button>
                <button type="button" onclick="closeUserModal()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-700 border border-slate-300 hover:bg-slate-50 rounded-xl transition">
                    {{ __('ui.cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const userStoreUrl  = @json(route('admin.users.store'));
    const userUpdateUrl = @json(route('admin.users.update', '__ID__'));
    const UT = {
        addTitle:  @json(__('ui.um_add')),
        editTitle: @json(__('ui.um_edit')),
        updateBtn: @json(__('ui.um_update')),
    };

    const $u = id => document.getElementById(id);
    const userModal = $u('userModal'), userForm = $u('userForm');

    function openUserModal(mode, d = null) {
        const edit = mode === 'edit';
        $u('userMode').value = mode;
        $u('userTitle').textContent  = edit ? UT.editTitle : UT.addTitle;
        $u('userSubmit').textContent = edit ? UT.updateBtn : UT.addTitle;
        userForm.action = edit ? userUpdateUrl.replace('__ID__', d.id) : userStoreUrl;
        $u('userMethod').disabled = !edit;
        $u('userId').value = edit ? d.id : '';

        $u('uPassword').required = !edit;
        $u('uPasswordHint').classList.toggle('hidden', !edit);

        if (edit) {
            $u('uName').value  = d.full_name ?? '';
            $u('uEmail').value = d.email;
            $u('uPhone').value = d.phone_number ?? '';
            $u('uType').value  = d.user_type;
            $u('uType').disabled = d.locked;   // disabled tidak ikut terkirim; controller tetap aman
            $u('uTypeHint').classList.toggle('hidden', !d.locked);
            $u('uPassword').value = '';
            $u('uActiveWrap').classList.add('hidden');
        } else {
            userForm.reset();
            $u('uType').disabled = false;
            $u('uTypeHint').classList.add('hidden');
            $u('uActiveWrap').classList.remove('hidden');
        }
        userModal.classList.remove('hidden');
    }

    function closeUserModal() { userModal.classList.add('hidden'); }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserModal(); });

    // Validasi gagal -> buka lagi modal dengan input sebelumnya
    @if ($errors->any())
        @php
            $errMode = old('_mode', 'create');
            $errUser = $errMode === 'edit' ? \App\Models\User::find(old('_id')) : null;
            $errLocked = $errUser && ($errUser->is(auth()->user()) || in_array($errUser->user_type, $lockedTypes, true));
        @endphp
        (function () {
            const mode = @json($errMode);
            openUserModal(mode, mode === 'edit'
                ? {
                    id: @json(old('_id')),
                    full_name: '', email: '', phone_number: '',
                    user_type: @json($errUser?->user_type ?? 'customer'),
                    locked: @json($errLocked),
                  }
                : null);

            $u('uName').value  = @json(old('full_name', ''));
            $u('uEmail').value = @json(old('email', ''));
            $u('uPhone').value = @json(old('phone_number', ''));
            if (mode === 'create') {
                if (@json(old('user_type'))) $u('uType').value = @json(old('user_type'));
                $u('uActive').checked = @json((bool) old('is_active'));
            }
        })();
    @endif
</script>
@endpush
@endsection