@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $roleTone = [
            'super_admin'   => 'bg-violet-100 text-violet-700',
            'admin_ops'     => 'bg-sky-100 text-sky-700',
            'admin_finance' => 'bg-emerald-100 text-emerald-700',
            'manager'       => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.staff_team') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.staff_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- PERAN --}}
        <div class="grid md:grid-cols-4 gap-4">
            @foreach ($roles as $role)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-md p-5 space-y-2">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $roleTone[$role] }}">{{ __('ui.role_' . $role) }}</span>
                    <p class="text-xs text-slate-600">{{ __('ui.role_' . $role . '_desc') }}</p>
                </div>
            @endforeach
        </div>

        {{-- TAMBAH --}}
        <form method="POST" action="{{ route('admin.staff.store') }}" class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
            @csrf
            <h2 class="font-extrabold text-slate-900"><i class="fa-solid fa-circle-plus text-sky-500 mr-1"></i> {{ __('ui.staff_add') }}</h2>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">{{ __('ui.full_name') }}</label>
                    <input name="full_name" required maxlength="255" value="{{ old('full_name') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.position') }}</label>
                    <input name="position" maxlength="100" value="{{ old('position') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.staff_role') }}</label>
                    <select name="sub_role" required class="{{ $input }}">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('sub_role', 'admin_ops') === $role)>{{ __('ui.role_' . $role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.email') }}</label>
                    <input type="email" name="email" required maxlength="255" value="{{ old('email') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.phone') }}</label>
                    <input name="phone_number" maxlength="20" value="{{ old('phone_number') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ __('ui.partner_temp_password') }}</label>
                    <input name="password" id="tempPassword" required minlength="8" maxlength="100" value="{{ old('password') }}" autocomplete="off" class="{{ $input }} font-mono">
                    <button type="button" onclick="generatePassword()" class="text-[11px] font-bold text-sky-600 hover:underline mt-1">{{ __('ui.generate_password') }}</button>
                </div>
            </div>

            <p class="text-[11px] text-slate-400">{{ __('ui.partner_temp_password_hint') }}</p>
            <button class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.staff_add') }}</button>
        </form>

        {{-- DAFTAR --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="px-6 pt-6 pb-4">
                <x-search-bar :placeholder="__('ui.staff_search')" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('ui.staff_team') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.staff_role') }}</th>
                            <th class="text-left px-3 py-3">{{ __('ui.account_status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($staff as $member)
                            @php
                                $role = \App\Support\AdminAbilities::roleOf($member);
                                $self = $member->id === auth()->id();
                            @endphp
                            <tr class="align-top">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-900">{{ $member->adminStaff?->full_name ?? $member->email }}@if ($self) <span class="text-[11px] font-semibold text-slate-400">({{ __('ui.you') }})</span>@endif</div>
                                    <div class="text-xs text-slate-500">{{ $member->email }}@if ($member->adminStaff?->position) &middot; {{ $member->adminStaff->position }}@endif</div>
                                </td>

                                <td colspan="3" class="px-3 py-3">
                                    <form method="POST" action="{{ route('admin.staff.update', $member) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf @method('PATCH')
                                        <select name="sub_role" @disabled($self) class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                                            @foreach ($roles as $option)
                                                <option value="{{ $option }}" @selected($role === $option)>{{ __('ui.role_' . $option) }}</option>
                                            @endforeach
                                        </select>
                                        <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                                            <input type="checkbox" name="is_active" value="1" @checked($member->is_active) @disabled($self) class="rounded border-slate-300 text-sky-500"> {{ __('ui.account_active') }}
                                        </label>
                                        @unless ($self)
                                            <button class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-sky-100 hover:text-sky-700 text-slate-700 text-xs font-bold transition">{{ __('ui.save_changes') }}</button>
                                        @endunless
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-sm">{{ __('ui.no_results') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function generatePassword() {
            const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const bytes = new Uint32Array(10);
            crypto.getRandomValues(bytes);
            document.getElementById('tempPassword').value = Array.from(bytes, (b) => chars[b % chars.length]).join('');
        }
    </script>
@endpush