@extends('account.layout')

@section('account')
    <form method="POST" action="{{ route('preferences.update') }}"
          class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-6 max-w-xl">
        @csrf

        <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('ui.language') }}</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach ($locales as $code => $name)
                    <label class="cursor-pointer">
                        <input type="radio" name="locale" value="{{ $code }}" class="peer sr-only" @checked($code === $currentLocale)>
                        <span class="block text-center px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600
                                     peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-600 hover:bg-slate-50 transition">
                            {{ $name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('ui.currency') }}</label>
            <select name="currency" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->code }}" @selected($currency->code === $currentCode)>
                        {{ $currency->code }} - {{ $currency->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.pref_currency_hint') }}</p>
        </div>

        <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
            {{ __('ui.save_changes') }}
        </button>
    </form>
@endsection