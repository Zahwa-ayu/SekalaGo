@inject('currencyService', 'App\Services\CurrencyService')

<form method="POST" action="{{ route('preferences.update') }}" style="display:inline">
    @csrf
    <select name="locale" aria-label="{{ __('ui.language') }}" onchange="this.form.submit()">
        @foreach (config('sekalago.locales') as $code => $label)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
        @endforeach
    </select>

    <select name="currency" aria-label="{{ __('ui.currency') }}" onchange="this.form.submit()">
        @foreach ($currencyService->all() as $c)
            <option value="{{ $c->code }}" @selected($currencyService->current()->code === $c->code)>
                {{ $c->code }} ({{ $c->symbol }})
            </option>
        @endforeach
    </select>
    <noscript><button>OK</button></noscript>
</form>