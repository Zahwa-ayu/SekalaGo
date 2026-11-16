<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CorporateRegistrationController extends Controller
{
    public const LEGAL_FORMS = ['PT', 'CV', 'Yayasan', 'Koperasi', 'Instansi Pemerintah', 'Lainnya'];

    public function create()
    {
        return view('auth.register-corporate', [
            'cities'     => City::optionsFor(),
            'legalForms' => self::LEGAL_FORMS,
        ]);
    }

    public function store(Request $request)
    {
        // NPWP ditulis dengan titik dan strip: disimpan sebagai angka saja
        $request->merge(['tax_id' => preg_replace('/\D/', '', (string) $request->input('tax_id'))]);

        $phone = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];
        $file  = ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'];

        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255', Rule::unique('companies', 'company_name')],
            'legal_form'          => ['required', Rule::in(self::LEGAL_FORMS)],
            'tax_id'              => ['required', 'digits_between:15,16', Rule::unique('companies', 'tax_id')],
            // NIB (OSS): tepat 13 digit angka
            'business_license_no' => ['required', 'digits:13', Rule::unique('companies', 'business_license_no')],
            'address'             => ['required', 'string', 'max:255'],
            'city_id'             => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'phone'               => $phone,
            'industry'            => ['nullable', 'string', 'max:60'],
            'tax_document'        => $file,
            'license_document'    => $file,
            'full_name'           => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number'        => $phone,
            'password'            => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms'               => ['accepted'],
        ], [
            'business_license_no.required' => 'NIB wajib diisi.',
            'business_license_no.digits'   => 'NIB harus terdiri dari tepat 13 digit angka.',
            'business_license_no.unique'   => 'NIB ini sudah terdaftar.',
        ]);

        $paths = [];

        try {
            DB::transaction(function () use ($data, $request, &$paths) {
                $user = User::create([
                    'email'        => $data['email'],
                    'password'     => $data['password'],
                    'phone_number' => $data['phone_number'],
                    'user_type'    => 'corporate',
                ]);

                $company = Company::create([
                    'company_name'        => $data['company_name'],
                    'corporate_code'      => $this->newCode($data['company_name']),
                    'legal_form'          => $data['legal_form'],
                    'tax_id'              => $data['tax_id'],
                    'business_license_no' => $data['business_license_no'],
                    'address'             => $data['address'],
                    'city_id'             => $data['city_id'],
                    'phone'               => $data['phone'],
                    'industry'            => $data['industry'] ?? null,
                    'travel_policy_limit' => (int) config('sekalago.corporate_default_limit', 2000000),
                    'source'              => 'self',
                    'verification_status' => 'pending',
                ]);

                // Dokumen disimpan di disk privat; hanya admin yang bisa membukanya
                $paths['tax']     = $request->file('tax_document')->store("corporate-documents/{$company->id}", 'local');
                $paths['license'] = $request->file('license_document')->store("corporate-documents/{$company->id}", 'local');

                $company->update([
                    'tax_document_path'     => $paths['tax'],
                    'license_document_path' => $paths['license'],
                ]);

                // Pembuat akun menjadi admin Finance perusahaan
                $company->companyUsers()->create([
                    'user_id'         => $user->id,
                    'full_name'       => $data['full_name'],
                    'role_in_company' => 'admin_finance',
                    'department'      => 'Finance',
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        // Tanpa login otomatis: akun baru bisa dipakai setelah admin memverifikasi
        return redirect()->route('login')->with('success', __('ui.corporate_registered'));
    }

    /** Kode perusahaan unik dari huruf awal namanya, misalnya MAJU47. */
    private function newCode(string $name): string
    {
        $prefix = strtoupper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 4)) ?: 'CORP';

        do {
            $code = $prefix . random_int(10, 99);
        } while (Company::where('corporate_code', $code)->exists());

        return $code;
    }
}