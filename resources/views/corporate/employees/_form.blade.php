@php
    $box = 'bg-slate-50 p-3 rounded-2xl border border-slate-200 focus-within:border-sagara-blue transition';
    $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $input = 'w-full bg-transparent font-semibold text-slate-800 text-sm focus:outline-none';
@endphp

@if ($errors->any())
    <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-user text-sagara-blue"></i> {{ __('ui.full_name') }}</label>
        <input name="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="{{ $input }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-envelope text-sagara-blue"></i> {{ __('ui.email') }}</label>
        <input type="email" name="email" required class="{{ $input }}"
               value="{{ old('email', $employee->exists ? $employee->user->email : '') }}">
    </div>

    <div class="{{ $box }} md:col-span-2">
        <label class="{{ $label }}">
            <i class="fa-solid fa-key text-sagara-coral"></i>
            {{ $employee->exists ? __('ui.new_password_optional') : __('ui.temp_password') }}
        </label>
        <input type="text" name="password" {{ $employee->exists ? '' : 'required' }} class="{{ $input }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-sitemap text-sagara-blue"></i> {{ __('ui.department') }}</label>
        <input name="department" value="{{ old('department', $employee->department) }}" class="{{ $input }}">
    </div>

    <div class="{{ $box }}">
        <label class="{{ $label }}"><i class="fa-solid fa-id-badge text-sagara-blue"></i> {{ __('ui.position') }}</label>
        <select name="position_id" class="{{ $input }} cursor-pointer">
            <option value="">{{ __('ui.no_position') }}</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}"
                    @selected((string) old('position_id', $employee->position_id) === (string) $position->id)>
                    {{ $position->name }} ({{ format_price($position->booking_limit, 'IDR') }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="{{ $box }} {{ $isSelf ? 'opacity-60' : '' }}">
        <label class="{{ $label }}"><i class="fa-solid fa-user-shield text-sagara-blue"></i> {{ __('ui.role') }}</label>
        <select name="role_in_company" {{ $isSelf ? 'disabled' : '' }} class="{{ $input }} {{ $isSelf ? 'cursor-not-allowed' : 'cursor-pointer' }}">
            @foreach (['employee', 'admin_finance'] as $role)
                <option value="{{ $role }}"
                    @selected(old('role_in_company', $employee->role_in_company ?? 'employee') === $role)>
                    {{ __('ui.role_' . $role) }}
                </option>
            @endforeach
        </select>
        @if ($isSelf)
            <input type="hidden" name="role_in_company" value="{{ $employee->role_in_company }}">
        @endif
    </div>

    @if ($employee->exists && ! $isSelf)
        <div class="{{ $box }} flex items-center">
            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="rounded text-sagara-blue focus:ring-sagara-blue"
                       @checked(old('is_active', $employee->user->is_active))>
                {{ __('ui.active') }}
            </label>
        </div>
    @endif
</div>