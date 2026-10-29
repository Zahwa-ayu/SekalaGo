<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    private const STATUSES = ['pending', 'verified', 'rejected', 'all'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, self::STATUSES, true), 404);

        $partners = Partner::with('user')
            ->when($status !== 'all', fn ($q) => $q->where('verification_status', $status))
            ->latest()
            ->get();

        $counts = Partner::selectRaw('verification_status, count(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return view('admin.partners.index', compact('partners', 'status', 'counts'));
    }

    public function show(Partner $partner)
    {
        $partner->load('user')->loadCount('hotels');

        return view('admin.partners.show', compact('partner'));
    }

    public function verify(Partner $partner)
    {
        $partner->update(['verification_status' => 'verified', 'verification_note' => null]);

        return redirect()->route('admin.partners.show', $partner)->with('success', __('ui.partner_verified'));
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
}