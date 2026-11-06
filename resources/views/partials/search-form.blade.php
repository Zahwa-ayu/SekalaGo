@php
    $params = $params ?? [];
    $cities = $cities ?? collect();
    $labelClass = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
    $boxClass = 'bg-slate-50 hover:bg-sky-50/40 p-3.5 rounded-2xl border border-slate-200/80 focus-within:border-sky-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-sky-100 transition text-left';
    $inputClass = 'w-full bg-transparent font-bold text-slate-800 text-sm focus:outline-none cursor-pointer';
@endphp

@include('partials.search-tabs')

@if ($errors->any())
    <ul class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs text-left list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div x-data="{ activeTab: 'hotel' }" class="w-full">
    {{-- TAB PILIHAN KATALOG (ALA TRAVELOKA / TIKET.COM) --}}
    <div class="flex items-center gap-2 mb-5 border-b border-slate-100 pb-3">
        <button type="button" 
                @click="activeTab = 'hotel'"
                :class="activeTab === 'hotel' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition">
            <i class="fa-solid fa-hotel"></i> Hotels & Villa
        </button>
        <button type="button" 
                @click="activeTab = 'car'"
                :class="activeTab === 'car' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition">
            <i class="fa-solid fa-car"></i> Sewa Mobil / Car Rental
        </button>
    </div>

    {{-- FORM SEARCH HOTEL --}}
    <form method="GET" action="{{ route('hotels.search') }}" x-show="activeTab === 'hotel'">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-location-dot text-sky-500"></i> {{ __('ui.city') }}</label>
                <input name="city" list="city-list" class="{{ $inputClass }}"
                       value="{{ old('city', $params['city'] ?? '') }}"
                       placeholder="Mau menginap di mana?" required>
                <datalist id="city-list">
                    @foreach ($cities as $cityName)
                        <option value="{{ $cityName }}">
                    @endforeach
                </datalist>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-days text-sky-500"></i> {{ __('ui.check_in') }}</label>
                <input type="date" name="check_in" class="{{ $inputClass }}" min="{{ today()->toDateString() }}"
                       value="{{ old('check_in', $params['check_in'] ?? today()->addDay()->toDateString()) }}" required>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-check text-rose-500"></i> {{ __('ui.check_out') }}</label>
                <input type="date" name="check_out" class="{{ $inputClass }}" min="{{ today()->addDay()->toDateString() }}"
                       value="{{ old('check_out', $params['check_out'] ?? today()->addDays(2)->toDateString()) }}" required>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-users text-sky-500"></i> {{ __('ui.guests') }}</label>
                <input type="number" name="guests" class="{{ $inputClass }}" min="1" max="20"
                       value="{{ old('guests', $params['guests'] ?? 2) }}" required>
            </div>
        </div>

        <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
            <!-- Tombol Cari Hotel Solid Sky (Bukan Gradasi) -->
            <button class="w-full md:w-auto px-8 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow-md shadow-sky-500/20 transition duration-300 flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i> {{ __('ui.search') }} Hotel
            </button>
        </div>
    </form>

    {{-- FORM SEARCH SEWA MOBIL (FALLBACK SEMENTARA) --}}
    <form method="GET" action="{{ route('hotels.search') }}" x-show="activeTab === 'car'" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-location-dot text-sky-500"></i> Lokasi Penjemputan</label>
                <input name="city" list="city-list-car" class="{{ $inputClass }}"
                       value="{{ old('city', $params['city'] ?? '') }}"
                       placeholder="Pilih kota penjemputan" required>
                <datalist id="city-list-car">
                    @foreach ($cities as $cityName)
                        <option value="{{ $cityName }}">
                    @endforeach
                </datalist>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-days text-sky-500"></i> Tanggal Mulai</label>
                <input type="date" name="check_in" class="{{ $inputClass }}" min="{{ today()->toDateString() }}"
                       value="{{ old('check_in', $params['check_in'] ?? today()->addDay()->toDateString()) }}" required>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-calendar-check text-rose-500"></i> Tanggal Selesai</label>
                <input type="date" name="check_out" class="{{ $inputClass }}" min="{{ today()->addDay()->toDateString() }}"
                       value="{{ old('check_out', $params['check_out'] ?? today()->addDays(2)->toDateString()) }}" required>
            </div>

            <div class="{{ $boxClass }}">
                <label class="{{ $labelClass }}"><i class="fa-solid fa-user-gear text-sky-500"></i> Tipe Rental</label>
                <select name="driver_type" class="{{ $inputClass }}">
                    <option value="without_driver">Lepas Kunci (Tanpa Sopir)</option>
                    <option value="with_driver">Dengan Sopir (With Driver)</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
            <!-- Tombol Cari Mobil Solid Sky (Bukan Gradasi) -->
            <button class="w-full md:w-auto px-8 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl shadow-md shadow-sky-500/20 transition duration-300 flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Cari Mobil
            </button>
        </div>
    </form>
</div>