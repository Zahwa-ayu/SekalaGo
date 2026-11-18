<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PartnerRegistrationController extends Controller
{
    public const LEGAL_FORMS    = ['PT', 'CV', 'UD / Perorangan', 'Koperasi', 'Yayasan', 'Lainnya'];
    public const BUSINESS_TYPES = ['hotel', 'car', 'both'];

    public function create()
    {
        $faqs = Faq::where('category', 'partner')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with('translations')
            ->get();

        return view('auth.register-partner', [
            'faqs'          => $faqs,
            'cities'        => City::optionsFor(),
            'legalForms'    => self::LEGAL_FORMS,
            'businessTypes' => self::BUSINESS_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        // NPWP ditulis dengan titik dan strip: disimpan sebagai angka saja
        $request->merge(['tax_id' => preg_replace('/\D/', '', (string) $request->input('tax_id'))]);

        $phone = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];
        $file  = ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'];

        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255', Rule::unique('partners', 'company_name')],
            'legal_form'          => ['required', Rule::in(self::LEGAL_FORMS)],
            'business_type'       => ['required', Rule::in(self::BUSINESS_TYPES)],
            'tax_id'              => ['required', 'digits_between:15,16', Rule::unique('partners', 'tax_id')],
            'business_license_no' => ['required', 'string', 'regex:/^[0-9A-Za-z.\-\/]{8,30}$/'],
            'address'             => ['required', 'string', 'max:255'],
            'city_id'             => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'phone'               => $phone,
            'website'             => ['nullable', 'url', 'max:255'],

            'tax_document'        => $file,
            'license_document'    => $file,
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],

            'contact_name'        => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number'        => $phone,
            'password'            => ['required', 'confirmed', Password::min(8)->letters()->numbers()],

            // Rekening pencairan: opsional, tetapi kalau diisi harus lengkap
            'bank_name'           => ['nullable', 'string', 'max:60', 'required_with:bank_account_no,bank_account_name'],
            'bank_account_no'     => ['nullable', 'string', 'max:50', 'regex:/^[0-9\s\-]+$/', 'required_with:bank_name,bank_account_name'],
            'bank_account_name'   => ['nullable', 'string', 'max:100', 'required_with:bank_name,bank_account_no'],

            'proposed_commission'      => ['nullable', 'numeric', 'between:10,25'],
            'commission_proposal_note' => ['nullable', 'string', 'max:300'],
            'terms'                    => ['accepted'],
        ]);

        $proposal = filled($data['proposed_commission'] ?? null);
        $paths    = [];

        try {
            DB::transaction(function () use ($data, $request, $proposal, &$paths) {
                $user = User::create([
                    'email'        => $data['email'],
                    'password'     => $data['password'],
                    'phone_number' => $data['phone_number'],
                    'user_type'    => 'partner',
                ]);

                $partner = Partner::create([
                    'user_id'                  => $user->id,
                    'company_name'             => $data['company_name'],
                    'legal_form'               => $data['legal_form'],
                    'business_type'            => $data['business_type'],
                    'tax_id'                   => $data['tax_id'],
                    'business_license_no'      => $data['business_license_no'],
                    'address'                  => $data['address'],
                    'city_id'                  => $data['city_id'],
                    'phone'                    => $data['phone'],
                    'website'                  => $data['website'] ?? null,
                    'contact_name'             => $data['contact_name'],
                    'bank_name'                => $data['bank_name'] ?? null,
                    'bank_account_no'          => $data['bank_account_no'] ?? null,
                    'bank_account_name'        => $data['bank_account_name'] ?? null,
                    'verification_status'      => 'pending',
                    'proposed_commission_rate' => $proposal ? round($data['proposed_commission'] / 100, 4) : null,
                    'commission_proposal_note' => $proposal ? ($data['commission_proposal_note'] ?? null) : null,
                    'commission_proposed_at'   => $proposal ? now() : null,
                ]);

                // Dokumen disimpan di disk privat; hanya admin yang bisa membukanya
                $folder = "partner-documents/{$partner->id}";

                $paths['tax']     = $request->file('tax_document')->store($folder, 'local');
                $paths['license'] = $request->file('license_document')->store($folder, 'local');

                if ($request->hasFile('supporting_document')) {
                    $paths['supporting'] = $request->file('supporting_document')->store($folder, 'local');
                }

                $partner->update([
                    'tax_document_path'        => $paths['tax'],
                    'license_document_path'    => $paths['license'],
                    'supporting_document_path' => $paths['supporting'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        // Tanpa login otomatis: akun bisa dipakai setelah admin memverifikasi
        return redirect()->route('login')->with('success', __('ui.partner_register_done'));
    }
}