@extends('layouts.app')

@section('content')
    @php
        $card  = 'bg-white rounded-3xl border border-slate-100 shadow-md p-6';
        $input = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sagara-blue';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $file  = 'block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold hover:file:bg-slate-200';

        // Kolom per bagian: dipakai skrip validasi di bawah
        $formFields = [
            'company_name', 'legal_form', 'business_type', 'tax_id', 'business_license_no', 'address', 'city_id', 'phone', 'website',
            'tax_document', 'license_document', 'supporting_document',
            'contact_name', 'phone_number', 'email', 'password',
            'bank_name', 'bank_account_no', 'bank_account_name',
        ];
    @endphp

    <style>
        /* Kolom yang salah: garis merah, pesan muncul di bawah isian */
        .field-error input:not([type="radio"]):not([type="checkbox"]):not([type="file"]),
        .field-error select { border-color: #f87171 !important; background-color: #fef2f2 !important; }
    </style>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-sagara-blue hover:text-sagara-navy transition">
            <i class="fa-solid fa-arrow-left"></i> {{ __('ui.verify_partners') }}
        </a>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.partner_add') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.partner_add_hint') }}</p>
        </div>

        {{-- novalidate: pengecekan dikerjakan skrip di bawah, dengan pesan di bawah tiap kolom --}}
        <form id="partner-form" method="POST" action="{{ route('admin.partners.store') }}" enctype="multipart/form-data" novalidate class="space-y-6">
            @csrf

            @if ($errors->any())
                <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif

            <div class="grid lg:grid-cols-3 gap-6 items-start">
                {{-- KOLOM KIRI: ISIAN --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- DATA USAHA --}}
                    <div class="{{ $card }} space-y-4">
                        <h2 class="font-extrabold text-slate-900">{{ __('ui.partner_business_data') }}</h2>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div data-box class="sm:col-span-2">
                                <label class="{{ $label }}">{{ __('ui.company_name_legal') }}</label>
                                <input name="company_name" required maxlength="255" value="{{ old('company_name') }}" class="{{ $input }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.legal_form') }}</label>
                                <select name="legal_form" required class="{{ $input }}">
                                    <option value="">-</option>
                                    @foreach ($legalForms as $form)
                                        <option value="{{ $form }}" @selected(old('legal_form') === $form)>{{ $form }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}">{{ __('ui.business_type') }}</label>
                            <div data-box class="rounded-xl">
                                <div class="grid sm:grid-cols-3 gap-2">
                                    @foreach ($businessTypes as $type)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="business_type" value="{{ $type }}" required class="peer sr-only" @checked(old('business_type') === $type)>
                                            <span class="block text-center px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 peer-checked:border-sagara-blue peer-checked:bg-slate-50 peer-checked:text-sagara-blue hover:bg-slate-50 transition">{{ __('ui.business_type_' . $type) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.tax_id') }}</label>
                                <input name="tax_id" required inputmode="numeric" maxlength="25" value="{{ old('tax_id') }}" placeholder="00.000.000.0-000.000" class="{{ $input }} font-mono">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.business_license_no') }}</label>
                                <input name="business_license_no" required maxlength="30" value="{{ old('business_license_no') }}" class="{{ $input }} font-mono">
                            </div>
                        </div>

                        <div data-box>
                            <label class="{{ $label }}">{{ __('ui.company_address') }}</label>
                            <input name="address" required maxlength="255" value="{{ old('address') }}" class="{{ $input }}">
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                                <select name="city_id" required class="{{ $input }}">
                                    <option value="">{{ __('ui.choose_city') }}</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" @selected((int) old('city_id') === $city->id)>{{ $city->name }}@if ($city->province), {{ $city->province }}@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.company_phone') }}</label>
                                <input name="phone" required inputmode="tel" maxlength="20" value="{{ old('phone') }}" class="{{ $input }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.website') }}</label>
                                <input type="url" name="website" maxlength="255" value="{{ old('website') }}" placeholder="https://" class="{{ $input }}">
                            </div>
                        </div>
                    </div>

                    {{-- DOKUMEN (OPSIONAL) --}}
                    <div class="{{ $card }} space-y-4">
                        <div>
                            <h2 class="font-extrabold text-slate-900">
                                {{ __('ui.corporate_documents') }}
                                <span class="ml-1 text-[11px] font-bold text-slate-400">{{ __('ui.optional') }}</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">{{ __('ui.partner_add_documents_hint') }}</p>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.tax_document') }}</label>
                                <input type="file" name="tax_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.license_document') }}</label>
                                <input type="file" name="license_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.supporting_document') }}</label>
                                <input type="file" name="supporting_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                            </div>
                        </div>

                        @if ($errors->any())
                            <p class="text-[11px] text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> Setelah halaman dimuat ulang, file perlu dipilih kembali.</p>
                        @endif
                    </div>

                    {{-- PENANGGUNG JAWAB DAN AKUN --}}
                    <div class="{{ $card }} space-y-4">
                        <h2 class="font-extrabold text-slate-900">{{ __('ui.partner_contact_account') }}</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.contact_person') }}</label>
                                <input name="contact_name" required maxlength="255" value="{{ old('contact_name') }}" class="{{ $input }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.contact_phone') }}</label>
                                <input name="phone_number" required inputmode="tel" maxlength="20" value="{{ old('phone_number') }}" class="{{ $input }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.email') }}</label>
                                <input type="email" name="email" required maxlength="255" value="{{ old('email') }}" class="{{ $input }}">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.partner_temp_password') }}</label>
                                <div class="flex gap-2">
                                    <input name="password" id="tempPassword" required minlength="8" maxlength="100" value="{{ old('password') }}" autocomplete="off" class="{{ $input }} font-mono">
                                    <button type="button" onclick="generatePassword()" class="px-3 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold whitespace-nowrap transition">{{ __('ui.generate_password') }}</button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.partner_temp_password_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- REKENING (OPSIONAL) --}}
                    <div class="{{ $card }} space-y-4">
                        <div>
                            <h2 class="font-extrabold text-slate-900">
                                {{ __('ui.payout_bank_account') }}
                                <span class="ml-1 text-[11px] font-bold text-slate-400">{{ __('ui.optional') }}</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">{{ __('ui.bank_account_hint') }}</p>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.bank_name') }}</label>
                                <input name="bank_name" list="bank-list" maxlength="60" value="{{ old('bank_name') }}" class="{{ $input }}">
                                <datalist id="bank-list">@foreach (config('sekalago.banks') as $bank)<option value="{{ $bank }}"></option>@endforeach</datalist>
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.bank_account_no') }}</label>
                                <input name="bank_account_no" inputmode="numeric" maxlength="50" value="{{ old('bank_account_no') }}" class="{{ $input }} font-mono">
                            </div>
                            <div data-box>
                                <label class="{{ $label }}">{{ __('ui.bank_account_name') }}</label>
                                <input name="bank_account_name" maxlength="100" value="{{ old('bank_account_name') }}" class="{{ $input }}">
                            </div>
                        </div>
                    </div>

                    {{-- AKSI --}}
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-check"></i> {{ __('ui.partner_add') }}
                        </button>
                        <a href="{{ route('admin.partners.index') }}" class="px-6 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">
                            {{ __('ui.cancel') }}
                        </a>
                    </div>
                </div>

                {{-- KOLOM KANAN: LANGKAH BERIKUTNYA --}}
                <div class="{{ $card }} space-y-2 text-xs text-slate-600 lg:sticky lg:top-24">
                    <div class="font-extrabold text-slate-900 text-sm"><i class="fa-solid fa-circle-info text-sagara-blue"></i> {{ __('ui.partner_add_next_title') }}</div>
                    <p>{{ __('ui.partner_add_next_steps') }}</p>
                </div>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        // Password sementara acak: huruf dan angka tanpa karakter yang mudah tertukar (0/O, 1/l/I)
        function generatePassword() {
            const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const bytes = new Uint32Array(10);
            crypto.getRandomValues(bytes);
            const field = document.getElementById('tempPassword');
            field.value = Array.from(bytes, (b) => chars[b % chars.length]).join('');
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        (function () {
            const form = document.getElementById('partner-form');
            if (!form) return;

            const fieldNames   = @json($formFields);
            const serverErrors = @json($errors->getMessages());

            // Radio group dikembalikan sebagai RadioNodeList: ambil elemen pertamanya
            const el = (name) => {
                const item = form.elements[name];
                return item instanceof RadioNodeList ? item[0] : item;
            };

            /* ---------- Aturan validasi (cermin dari Admin\PartnerController@store) ---------- */

            const text  = (field) => (field.value || '').trim();
            const empty = (field, message) => (text(field) === '' ? message : '');

            const phoneRule = (field) => {
                if (text(field) === '') return 'Nomor telepon wajib diisi.';
                return /^\+?[0-9\s\-]{8,20}$/.test(text(field)) ? '' : 'Nomor telepon tidak valid (8–20 digit).';
            };

            // Dokumen opsional, tetapi kalau dipilih formatnya harus benar
            const fileRule = (field) => {
                const file = field.files && field.files[0];
                if (!file) return '';
                if (!/\.(pdf|jpe?g|png|webp)$/i.test(file.name)) return 'Format file harus PDF, JPG, PNG, atau WEBP.';
                if (file.size > 4096 * 1024) return 'Ukuran file maksimal 4 MB.';
                return '';
            };

            // Rekening: opsional, tetapi kalau salah satu diisi, ketiganya harus lengkap
            const bankFilled   = () => ['bank_name', 'bank_account_no', 'bank_account_name'].some((name) => text(el(name)) !== '');
            const bankRequired = (field, message) => (bankFilled() && text(field) === '' ? message : '');

            const rules = {
                company_name:  (f) => empty(f, 'Nama perusahaan wajib diisi.'),
                legal_form:    (f) => empty(f, 'Pilih bentuk badan usaha.'),
                business_type: () => (form.querySelector('[name="business_type"]:checked') ? '' : 'Pilih jenis usaha.'),
                tax_id: (f) => {
                    const digits = text(f).replace(/\D/g, '');
                    if (digits === '') return 'NPWP wajib diisi.';
                    return digits.length >= 15 && digits.length <= 16 ? '' : 'NPWP harus 15–16 digit angka.';
                },
                business_license_no: (f) => {
                    if (text(f) === '') return 'Nomor izin usaha wajib diisi.';
                    return /^[0-9A-Za-z.\-\/]{8,30}$/.test(text(f)) ? '' : 'Nomor izin usaha 8–30 karakter (huruf, angka, titik, strip, atau garis miring).';
                },
                address: (f) => empty(f, 'Alamat perusahaan wajib diisi.'),
                city_id: (f) => empty(f, 'Pilih kota.'),
                phone:   phoneRule,
                website: (f) => {
                    if (text(f) === '') return '';
                    return /^https?:\/\/\S+\.\S+/i.test(text(f)) ? '' : 'Alamat website harus diawali http:// atau https://.';
                },

                tax_document:        fileRule,
                license_document:    fileRule,
                supporting_document: fileRule,

                contact_name: (f) => empty(f, 'Nama penanggung jawab wajib diisi.'),
                phone_number: phoneRule,
                email: (f) => {
                    if (text(f) === '') return 'Email wajib diisi.';
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text(f)) ? '' : 'Format email tidak valid.';
                },
                password: (f) => {
                    const value = f.value || '';
                    if (value === '') return 'Kata sandi sementara wajib diisi.';
                    return value.length >= 8 ? '' : 'Kata sandi minimal 8 karakter.';
                },

                bank_name:       (f) => bankRequired(f, 'Nama bank wajib diisi jika rekening diisi.'),
                bank_account_no: (f) => {
                    const missing = bankRequired(f, 'Nomor rekening wajib diisi jika rekening diisi.');
                    if (missing) return missing;
                    return text(f) === '' || /^[0-9\s\-]+$/.test(text(f)) ? '' : 'Nomor rekening hanya boleh berisi angka.';
                },
                bank_account_name: (f) => bankRequired(f, 'Nama pemilik rekening wajib diisi jika rekening diisi.'),
            };

            /* ---------- Pesan error di bawah kolom ---------- */

            const boxOf = (field) => field.closest('[data-box]');

            function setError(field, message) {
                const box = boxOf(field);
                if (!box) return;

                let note = box.querySelector('[data-error]');
                if (!note) {
                    note = document.createElement('p');
                    note.setAttribute('data-error', '');
                    note.className = 'text-[11px] font-semibold text-red-600 mt-1.5';
                    box.appendChild(note);
                }

                note.textContent = message;
                box.classList.add('field-error');
            }

            function clearError(field) {
                const box = boxOf(field);
                if (!box) return;

                box.querySelector('[data-error]')?.remove();
                box.classList.remove('field-error');
            }

            // Pesan hilang begitu kolomnya diperbaiki
            form.addEventListener('input',  (event) => clearError(event.target));
            form.addEventListener('change', (event) => clearError(event.target));

            /* ---------- Validasi saat kirim ---------- */

            function validateAll() {
                let firstInvalid = null;

                fieldNames.map(el).filter(Boolean).forEach((field) => {
                    const check   = rules[field.name];
                    const message = check ? check(field) : '';

                    if (message) {
                        setError(field, message);
                        firstInvalid = firstInvalid || field;
                    } else {
                        clearError(field);
                    }
                });

                return firstInvalid;
            }

            form.addEventListener('submit', (event) => {
                const firstInvalid = validateAll();

                if (firstInvalid) {
                    event.preventDefault();
                    boxOf(firstInvalid)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }
            });

            /* ---------- Awal ---------- */

            // Error dari server (setelah kirim) ditampilkan juga di bawah kolomnya
            let firstServerError = null;
            Object.entries(serverErrors).forEach(([name, messages]) => {
                const field = el(name);
                if (field) {
                    setError(field, messages[0]);
                    firstServerError = firstServerError || field;
                }
            });

            if (firstServerError) {
                boxOf(firstServerError)?.scrollIntoView({ block: 'center' });
            }
        })();
    </script>
@endpush