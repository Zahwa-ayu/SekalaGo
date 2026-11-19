<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\AgreementPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PartnerRegistrationController;
use App\Models\City;
use App\Models\User;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    private const STATUSES = ['pending', 'verified', 'rejected', 'all'];

    private const PAYOUT_LABELS = [
        'weekly'   => 'setiap minggu',
        'biweekly' => 'setiap dua minggu',
        'monthly'  => 'setiap bulan',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $partners = Partner::with(['user', 'latestAgreement'])
            ->when($status !== 'all', fn ($q) => $q->where('verification_status', $status))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        $counts = Partner::selectRaw('verification_status, count(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return view('admin.partners.index', compact('partners', 'status', 'counts'));
    }

    public function create()
    {
        return view('admin.partners.create', [
            'cities'        => City::optionsFor(),
            'legalForms'    => PartnerRegistrationController::LEGAL_FORMS,
            'businessTypes' => PartnerRegistrationController::BUSINESS_TYPES,
        ]);
    }

/** Admin mendaftarkan mitra: statusnya menunggu MOU, jadi alur verifikasi dan MOU yang ada tetap berlaku. */
    public function store(Request $request)
    {
        $request->merge(['tax_id' => preg_replace('/\D/', '', (string) $request->input('tax_id'))]);

        $phone = ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'];
        $file  = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'];

        $data = $request->validate([
            'company_name'        => ['required', 'string', 'max:255', Rule::unique('partners', 'company_name')],
            'legal_form'          => ['required', Rule::in(PartnerRegistrationController::LEGAL_FORMS)],
            'business_type'       => ['required', Rule::in(PartnerRegistrationController::BUSINESS_TYPES)],
            'tax_id'              => ['required', 'digits_between:15,16', Rule::unique('partners', 'tax_id')],
            'business_license_no' => ['required', 'string', 'regex:/^[0-9A-Za-z.\-\/]{8,30}$/'],
            'address'             => ['required', 'string', 'max:255'],
            'city_id'             => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'phone'               => $phone,
            'website'             => ['nullable', 'url', 'max:255'],

            'tax_document'        => $file,
            'license_document'    => $file,
            'supporting_document' => $file,

            'contact_name'        => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number'        => $phone,
            'password'            => ['required', 'string', 'min:8'],

            'bank_name'           => ['nullable', 'string', 'max:60', 'required_with:bank_account_no,bank_account_name'],
            'bank_account_no'     => ['nullable', 'string', 'max:50', 'regex:/^[0-9\s\-]+$/', 'required_with:bank_name,bank_account_name'],
            'bank_account_name'   => ['nullable', 'string', 'max:100', 'required_with:bank_name,bank_account_no'],
        ]);

        $paths = [];

        try {
            $partner = DB::transaction(function () use ($data, $request, &$paths) {
                $user = User::create([
                    'email'                => $data['email'],
                    'password'             => $data['password'],
                    'phone_number'         => $data['phone_number'],
                    'user_type'            => 'partner',
                    'must_change_password' => true,
                ]);

                $partner = Partner::create([
                    'user_id'             => $user->id,
                    'company_name'        => $data['company_name'],
                    'legal_form'          => $data['legal_form'],
                    'business_type'       => $data['business_type'],
                    'tax_id'              => $data['tax_id'],
                    'business_license_no' => $data['business_license_no'],
                    'address'             => $data['address'],
                    'city_id'             => $data['city_id'],
                    'phone'               => $data['phone'],
                    'website'             => $data['website'] ?? null,
                    'contact_name'        => $data['contact_name'],
                    'bank_name'           => $data['bank_name'] ?? null,
                    'bank_account_no'     => $data['bank_account_no'] ?? null,
                    'bank_account_name'   => $data['bank_account_name'] ?? null,
                    'verification_status' => 'pending',   // menunggu MOU: langkah verifikasi yang sama dengan mitra yang mendaftar sendiri
                    'source'              => 'admin',
                    'created_by'          => $request->user()->id,
                ]);

                $columns = ['tax_document' => 'tax_document_path', 'license_document' => 'license_document_path', 'supporting_document' => 'supporting_document_path'];

                foreach ($columns as $input => $column) {
                    if ($request->hasFile($input)) {
                        $paths[$input] = $request->file($input)->store("partner-documents/{$partner->id}", 'local');
                        $partner->{$column} = $paths[$input];
                    }
                }

                $partner->save();

                return $partner;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_created_admin'));
    }

    public function show(Partner $partner)
    {
        $partner->load('user', 'latestAgreement')->loadCount('hotels');

        return view('admin.partners.show', [
            'partner'   => $partner,
            'agreement' => $partner->latestAgreement,
        ]);
    }

    public function downloadAgreement(Partner $partner, AgreementPdf $pdf)
    {
        $agreement = $partner->latestAgreement;
        abort_unless($agreement, 404);

        return $pdf->download($agreement);
    }

    /** Verifikasi data mitra sekaligus mengirim (atau memperbarui) MOU. */
    public function sendAgreement(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'commission_percent' => ['required', 'numeric', 'between:10,25'],
            'payout_schedule'    => ['required', Rule::in(array_keys(self::PAYOUT_LABELS))],
            'free_cancel_days'   => ['required', 'integer', 'between:0,30'],
            'admin_note' => ['nullable', 'string', 'max:300'],
        ]);

        if ($partner->agreements()->where('status', 'signed')->exists()) {
            return back()->withErrors(['agreement' => __('ui.mou_already_signed')]);
        }

        DB::transaction(function () use ($partner, $data) {
            // MOU lama yang belum ditandatangani diganti versi baru
            $partner->agreements()->where('status', 'sent')->update(['status' => 'void']);

            $version = ((int) $partner->agreements()->max('version')) + 1;
            $rate    = round($data['commission_percent'] / 100, 4);

            $content = view('agreements.standard', [
                'platform'       => config('app.name'),
                'number'         => 'MOU-' . str_pad((string) $partner->id, 4, '0', STR_PAD_LEFT) . '-v' . $version,
                'date'           => now()->format('d/m/Y'),
                'company'        => $partner->company_name,
                'license'        => $partner->business_license_no,
                'commission'     => rtrim(rtrim(number_format($rate * 100, 2, '.', ''), '0'), '.'),
                'exampleGross'   => number_format(500000, 0, ',', '.'),
                'exampleFee'     => number_format(500000 * $rate, 0, ',', '.'),
                'exampleNet'     => number_format(500000 - 500000 * $rate, 0, ',', '.'),
                'payout'         => self::PAYOUT_LABELS[$data['payout_schedule']],
                'freeCancelDays' => $data['free_cancel_days'],
            ])->render();

            $partner->agreements()->create([
                'version'          => $version,
                'commission_rate'  => $rate,
                'payout_schedule'  => $data['payout_schedule'],
                'free_cancel_days' => $data['free_cancel_days'],
                'content'          => $content,
                'status'           => 'sent',
                'sent_at'          => now(),
                'proposed_rate' => $partner->proposed_commission_rate,
                'admin_note'    => $data['admin_note'] ?? null,
            ]);

            $partner->update(['verification_status' => 'verified', 'verification_note' => null]);
        });

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.mou_sent'));
    }

    public function reject(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'verification_note' => ['required', 'string', 'max:500'],
        ]);

        $partner->update([
            'verification_status' => 'rejected',
            'verification_note'   => $data['verification_note'],
        ]);

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_rejected_msg'));
    }

    /** Buka kembali pengajuan yang ditolak (misalnya setelah mitra dihubungi dan melengkapi data). */
    public function reopen(Partner $partner)
    {
        $partner->update(['verification_status' => 'pending', 'verification_note' => null]);

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_reopened'));
    }

    /** Dokumen legalitas mitra: tampil di browser (PDF atau gambar). */
    public function document(Partner $partner, string $type)
    {
        $path = match ($type) {
            'tax'        => $partner->tax_document_path,
            'license'    => $partner->license_document_path,
            'supporting' => $partner->supporting_document_path,
            default      => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}