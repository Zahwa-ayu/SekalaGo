@php
    $locales      = config('sekalago.locales');
    $sourceLocale = old('source_locale', $model->source_locale ?? app()->getLocale());
    $translations = $model->exists ? $model->translations->keyBy('locale') : collect();
    $sourceRow    = $translations->get($sourceLocale);
@endphp

<p>
    <label>{{ __('ui.source_language') }}<br>
        <select name="source_locale">
            @foreach ($locales as $code => $label)
                <option value="{{ $code }}" @selected($sourceLocale === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <br><small>{{ __('ui.source_language_hint') }}</small>
</p>

<p>
    <input name="name" value="{{ old('name', $sourceRow?->name) }}"
           placeholder="{{ $nameLabel }}" required>
</p>

<p>
    <textarea name="description" rows="4"
              placeholder="{{ __('ui.description') }}">{{ old('description', $sourceRow?->description) }}</textarea>
</p>

<details>
    <summary>{{ __('ui.edit_translations') }}</summary>
    <p><small>{{ __('ui.edit_translations_hint') }}</small></p>

    @foreach ($locales as $code => $label)
        @continue($code === $sourceLocale)

        <fieldset>
            <legend>
                {{ $label }}
                @if ($translations->get($code)?->is_auto_translated)
                    ({{ __('ui.auto_translated') }})
                @endif
            </legend>
            <p>
                <input name="overrides[{{ $code }}][name]"
                       value="{{ old('overrides.' . $code . '.name', $translations->get($code)?->name) }}"
                       placeholder="{{ $nameLabel }}">
            </p>
            <p>
                <textarea name="overrides[{{ $code }}][description]" rows="3"
                          placeholder="{{ __('ui.description') }}">{{ old('overrides.' . $code . '.description', $translations->get($code)?->description) }}</textarea>
            </p>
        </fieldset>
    @endforeach
</details>