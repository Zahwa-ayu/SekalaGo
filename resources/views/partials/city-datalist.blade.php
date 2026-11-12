@php $suggestions = \App\Services\CityDirectory::suggestions($for); @endphp

<datalist id="{{ $id }}">
    @foreach ($suggestions as $item)
        <option value="{{ $item['name'] }}" label="{{ $item['label'] }}"></option>
    @endforeach
</datalist>