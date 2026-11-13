@extends('account.layout')

@section('account')
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8">
        <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-5">
            {{ __('ui.language_currency') }}
        </h2>

        <form method="POST" action="{{ route('preferences.update') }}" class="space-y-5 max-w-md">
            @csrf

            {{-- BAHASA --}}
            <div>
                <label for="locale" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('ui.language') }}
                </label>
                <select id="locale" name="locale"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500">
                    @foreach ($locales as $code => $label)
                        <option value="{{ $code }}" @selected($code === $currentLocale)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('locale')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- MATA UANG --}}
            <div>
                <label for="currency" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('ui.currency') }}
                </label>
                <select id="currency" name="currency"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-sky-500 focus:ring-sky-500">
                    @foreach ($currencies as $c)
                        <option value="{{ $c->code }}" @selected($c->code === $currentCode)>
                            {{ $c->code }}@if (!empty($c->name)) &ndash; {{ $c->name }}@endif
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">
                    {{ __('ui.save') }}
                </button>
                <a href="{{ route('account.settings') }}"
                   class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-sky-50 hover:text-sky-600 text-sm font-bold transition">
                    {{ __('ui.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection