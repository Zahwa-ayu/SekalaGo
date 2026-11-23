@extends('layouts.app')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500';
        $label = 'block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1';
        $file  = 'block w-full text-sm text-slate-600 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-bold hover:file:bg-sky-100';
    @endphp

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <a href="{{ route('admin.companies.index') }}" class="text-xs font-bold text-sky-600 hover:underline"><i class="fa-solid fa-chevron-left mr-1"></i> {{ __('ui.corporate_management') }}</a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ __('ui.company_create_title') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('ui.company_create_hint') }}</p>
        </div>

        @if ($errors->any())
            <ul class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        {{-- novalidate: pengecekan dikerjakan skrip di bawah (aturan sama dengan pendaftaran korporat), pesan tampil di bawah tiap kolom --}}
        <form id="company-form" method="POST" action="{{ route('admin.companies.store') }}" enctype="multipart/form-data" novalidate
              class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 space-y-8">
            @csrf

            {{-- DATA PERUSAHAAN --}}
            <div class="space-y-4">
                <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">1.</span> {{ __('ui.corporate_company_data') }}</h2>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2" data-field>
                        <label class="{{ $label }}">{{ __('ui.company_name_legal') }}</label>
                        <input name="company_name" required maxlength="255" value="{{ old('company_name') }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.legal_form') }}</label>
                        <select name="legal_form" required class="{{ $input }}">
                            <option value="">-</option>
                            @foreach ($legalForms as $form)
                                <option value="{{ $form }}" @selected(old('legal_form') === $form)>{{ $form }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.tax_id') }}</label>
                        <input name="tax_id" required inputmode="numeric" maxlength="25" value="{{ old('tax_id') }}" placeholder="00.000.000.0-000.000" class="{{ $input }} font-mono">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.business_license_no') }}</label>
                        <input name="business_license_no" required inputmode="numeric" maxlength="13" pattern="[0-9]{13}" value="{{ old('business_license_no') }}" placeholder="1234567890123" class="{{ $input }} font-mono">
                    </div>
                </div>

                <div data-field>
                    <label class="{{ $label }}">{{ __('ui.company_address') }}</label>
                    <input name="address" required maxlength="255" value="{{ old('address') }}" class="{{ $input }}">
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.city_name') }}</label>
                        <select name="city_id" required class="{{ $input }}">
                            <option value="">{{ __('ui.choose_city') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected((int) old('city_id') === $city->id)>{{ $city->name }}@if ($city->province), {{ $city->province }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.company_phone') }}</label>
                        <input name="phone" required inputmode="tel" maxlength="20" value="{{ old('phone') }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.industry') }}</label>
                        <input name="industry" maxlength="60" value="{{ old('industry') }}" class="{{ $input }}">
                    </div>
                </div>
            </div>

            {{-- DOKUMEN (OPSIONAL) --}}
            <div class="space-y-3">
                <div>
                    <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">2.</span> {{ __('ui.corporate_documents') }} <span class="text-xs font-semibold text-slate-400">({{ __('ui.optional') }})</span></h2>
                    <p class="text-xs text-slate-500">{{ __('ui.partner_add_documents_hint') }}</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.tax_document') }}</label>
                        <input type="file" name="tax_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.license_document') }}</label>
                        <input type="file" name="license_document" accept=".pdf,image/jpeg,image/png,image/webp" class="{{ $file }}">
                    </div>
                </div>
            </div>

            {{-- KEBIJAKAN DAN SALDO AWAL --}}
            <div class="space-y-4">
                <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">3.</span> {{ __('ui.company_policy_settings') }}</h2>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.corporate_code_label') }}</label>
                        <input name="corporate_code" maxlength="20" value="{{ old('corporate_code') }}" class="{{ $input }} font-mono uppercase">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.corporate_code_hint') }}</p>
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.travel_policy_limit_label') }}</label>
                        <input type="number" name="travel_policy_limit" required min="0" value="{{ old('travel_policy_limit', config('sekalago.corporate_default_limit', 2000000)) }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.default_monthly_budget_label') }}</label>
                        <input type="number" name="default_monthly_budget" min="0" value="{{ old('default_monthly_budget') }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2" data-field>
                        <label class="{{ $label }}">{{ __('ui.initial_deposit_label') }}</label>
                        <input type="number" name="initial_deposit" min="0" value="{{ old('initial_deposit') }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.topup_reference') }}</label>
                        <input name="initial_deposit_reference" maxlength="100" value="{{ old('initial_deposit_reference') }}" class="{{ $input }}">
                    </div>
                </div>
            </div>

            {{-- AKUN FINANCE --}}
            <div class="space-y-4">
                <div>
                    <h2 class="font-extrabold text-slate-900"><span class="text-sky-500">4.</span> {{ __('ui.corporate_account') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('ui.corporate_account_hint') }}</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.full_name') }}</label>
                        <input name="finance_name" required maxlength="255" value="{{ old('finance_name') }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.phone') }}</label>
                        <input name="finance_phone" required inputmode="tel" maxlength="20" value="{{ old('finance_phone') }}" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.email') }}</label>
                        <input type="email" name="finance_email" required maxlength="255" value="{{ old('finance_email') }}" class="{{ $input }}">
                    </div>
                    <div data-field>
                        <label class="{{ $label }}">{{ __('ui.partner_temp_password') }}</label>
                        <div class="flex gap-2">
                            <input name="finance_password" id="tempPassword" required minlength="8" maxlength="100" value="{{ old('finance_password') }}" autocomplete="off" class="{{ $input }} font-mono">
                            <button type="button" onclick="generatePassword()" class="px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold whitespace-nowrap transition">{{ __('ui.generate_password') }}</button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('ui.partner_temp_password_hint') }}</p>
                        <p class="text-[11px] text-slate-400">{{ __('ui.password_rules_hint') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/20 transition">{{ __('ui.company_create_title') }}</button>
                <a href="{{ route('admin.companies.index') }}" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-bold transition">{{ __('ui.cancel') }}</a>
            </div>
        </form>
    </section>
@endsection

@push('head')
    <style>
        /* Kolom yang salah: garis merah, pesan muncul di bawah isian */
        .field-error { border-color: #f87171 !important; background-color: #fef2f2 !important; }
    </style>
@endpush

@push('scripts')
    <script>
        // Kata sandi sementara: selalu memuat huruf besar, huruf kecil, dan angka (memenuhi aturan kata sandi)
        function generatePassword() {
            const lower  = 'abcdefghijkmnpqrstuvwxyz';
            const upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const digits = '23456789';
            const all    = lower + upper + digits;
            const pick   = (set) => set[crypto.getRandomValues(new Uint32Array(1))[0] % set.length];

            const chars = [pick(lower), pick(upper), pick(digits)];
            while (chars.length < 10) chars.push(pick(all));

            // Acak urutan supaya tiga karakter wajib tidak selalu di depan
            for (let i = chars.length - 1; i > 0; i--) {
                const j = crypto.getRandomValues(new Uint32Array(1))[0] % (i + 1);
                [chars[i], chars[j]] = [chars[j], chars[i]];
            }

            const field = document.getElementById('tempPassword');
            field.value = chars.join('');
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        (function () {
            const form = document.getElementById('company-form');
            if (!form) return;

            const serverErrors = @json($errors->getMessages());

            const text  = (field) => (field.value || '').trim();
            const empty = (field, message) => (text(field) === '' ? message : '');

            /* ---------- Aturan validasi (sama dengan formulir pendaftaran korporat) ---------- */

            const phoneRule = (field) => {
                if (text(field) === '') return 'Nomor telepon wajib diisi.';
                return /^\+?[0-9\s\-]{8,20}$/.test(text(field)) ? '' : 'Nomor telepon tidak valid (8–20 digit).';
            };

            // Dokumen di sini opsional: kalau dipilih, format dan ukurannya tetap dicek
            const optionalFileRule = (field) => {
                const file = field.files && field.files[0];
                if (!file) return '';
                if (!/\.(pdf|jpe?g|png|webp)$/i.test(file.name)) return 'Format file harus PDF, JPG, PNG, atau WEBP.';
                if (file.size > 4096 * 1024) return 'Ukuran file maksimal 4 MB.';
                return '';
            };

            const optionalMoneyRule = (field) => {
                if (text(field) === '') return '';
                return /^\d+$/.test(text(field)) ? '' : 'Isi dengan angka bulat, 0 atau lebih.';
            };

            const rules = {
                company_name: (f) => empty(f, 'Nama perusahaan wajib diisi.'),
                legal_form:   (f) => empty(f, 'Pilih bentuk badan usaha.'),
                tax_id: (f) => {
                    const digits = text(f).replace(/\D/g, '');
                    if (digits === '') return 'NPWP wajib diisi.';
                    return digits.length >= 15 && digits.length <= 16 ? '' : 'NPWP harus 15–16 digit angka.';
                },
                business_license_no: (f) => {
                    if (text(f) === '') return 'NIB wajib diisi.';
                    return /^[0-9]{13}$/.test(text(f)) ? '' : 'NIB harus terdiri dari tepat 13 digit angka.';
                },
                address: (f) => empty(f, 'Alamat perusahaan wajib diisi.'),
                city_id: (f) => empty(f, 'Pilih kota.'),
                phone:   phoneRule,

                tax_document:     optionalFileRule,
                license_document: optionalFileRule,

                travel_policy_limit: (f) => {
                    if (text(f) === '') return 'Limit perjalanan wajib diisi.';
                    return /^\d+$/.test(text(f)) ? '' : 'Isi dengan angka bulat, 0 atau lebih.';
                },
                default_monthly_budget: optionalMoneyRule,
                initial_deposit:        optionalMoneyRule,

                finance_name:  (f) => empty(f, 'Nama lengkap wajib diisi.'),
                finance_phone: phoneRule,
                finance_email: (f) => {
                    if (text(f) === '') return 'Email wajib diisi.';
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text(f)) ? '' : 'Format email tidak valid.';
                },
                finance_password: (f) => {
                    const value = f.value || '';
                    if (value === '') return 'Kata sandi wajib diisi.';
                    return value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value)
                        ? '' : 'Kata sandi minimal 8 karakter dan memuat huruf serta angka.';
                },
            };

            /* ---------- Pesan error di bawah kolom ---------- */

            const wrapOf = (field) => field.closest('[data-field]');

            function setError(field, message) {
                const wrap = wrapOf(field);
                if (!wrap) return;

                let note = wrap.querySelector('[data-error]');
                if (!note) {
                    note = document.createElement('p');
                    note.setAttribute('data-error', '');
                    note.className = 'text-[11px] font-semibold text-red-600 mt-1';
                    wrap.appendChild(note);
                }

                note.textContent = message;
                field.classList.add('field-error');
            }

            function clearError(field) {
                const wrap = wrapOf(field);
                if (!wrap || !field.name) return;

                wrap.querySelector('[data-error]')?.remove();
                field.classList.remove('field-error');
            }

            form.addEventListener('input',  (event) => clearError(event.target));
            form.addEventListener('change', (event) => clearError(event.target));

            /* ---------- Kirim: cek semua kolom sekaligus ---------- */

            form.addEventListener('submit', (event) => {
                let firstInvalid = null;

                Object.keys(rules).forEach((name) => {
                    const field = form.elements[name];
                    if (!field) return;

                    const message = rules[name](field);

                    if (message) {
                        setError(field, message);
                        firstInvalid = firstInvalid || field;
                    } else {
                        clearError(field);
                    }
                });

                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }
            });

            // Error dari server (setelah kirim) ditampilkan juga di bawah kolomnya
            Object.entries(serverErrors).forEach(([name, messages]) => {
                if (form.elements[name]) setError(form.elements[name], messages[0]);
            });
        })();
    </script>
@endpush