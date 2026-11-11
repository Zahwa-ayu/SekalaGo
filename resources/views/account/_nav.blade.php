@php
    $tabs = [
        ['fa-id-card',  'ui.account_info',      'account.settings',      ['account.settings']],
        ['fa-user-pen', 'ui.account_edit',      'account.edit',          ['account.edit', 'account.update']],
        ['fa-key',      'ui.change_password',   'account.password.edit', ['account.password.*']],
        ['fa-language', 'ui.language_currency', 'account.preferences',   ['account.preferences']],
    ];
@endphp

<div class="flex flex-wrap gap-2">
    @foreach ($tabs as [$icon, $label, $route, $patterns])
        @php $on = request()->routeIs(...$patterns); @endphp

        <a href="{{ route($route) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition
                  {{ $on ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __($label) }}
        </a>
    @endforeach
</div>