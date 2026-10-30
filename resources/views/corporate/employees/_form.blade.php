@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<p>
    <label>{{ __('ui.full_name') }}<br>
        <input name="full_name" value="{{ old('full_name', $employee->full_name) }}" required>
    </label>
</p>
<p>
    <label>{{ __('ui.email') }}<br>
        <input type="email" name="email"
               value="{{ old('email', $employee->exists ? $employee->user->email : '') }}" required>
    </label>
</p>
<p>
    <label>{{ $employee->exists ? __('ui.new_password_optional') : __('ui.temp_password') }}<br>
        <input type="text" name="password" {{ $employee->exists ? '' : 'required' }}>
    </label>
</p>
<p>
    <label>{{ __('ui.department') }}<br>
        <input name="department" value="{{ old('department', $employee->department) }}">
    </label>
</p>
<p>
    <label>{{ __('ui.position') }}<br>
        <select name="position_id">
            <option value="">{{ __('ui.no_position') }}</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}"
                    @selected((string) old('position_id', $employee->position_id) === (string) $position->id)>
                    {{ $position->name }} ({{ format_price($position->booking_limit, 'IDR') }})
                </option>
            @endforeach
        </select>
    </label>
</p>
<p>
    <label>{{ __('ui.role') }}<br>
        <select name="role_in_company" {{ $isSelf ? 'disabled' : '' }}>
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
    </label>
</p>

@if ($employee->exists && ! $isSelf)
    <p>
        <label>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $employee->user->is_active))>
            {{ __('ui.active') }}
        </label>
    </p>
@endif