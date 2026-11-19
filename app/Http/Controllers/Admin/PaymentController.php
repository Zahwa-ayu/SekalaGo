<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->query('view');
        abort_unless(in_array($view, [null, '', 'refund', 'failed'], true), 404);

        $payments = Payment::query()
            ->with('booking')
            ->when($view === 'refund', fn ($q) => $q->where('refund_status', 'manual_pending'))
            ->when($view === 'failed', fn ($q) => $q->whereIn('status', ['failed', 'expired']))
            ->latest()
            ->paginate((int) config('sekalago.per_page', 15))
            ->withQueryString();

        return view('admin.payments.index', [
            'payments'    => $payments,
            'view'        => $view,
            'manualCount' => Payment::where('refund_status', 'manual_pending')->count(),
            'driver'      => config('sekalago.payment_driver'),
            'fees' => [
                        'month'     => (int) Payment::whereIn('status', ['paid', 'refunded'])->where('paid_at', '>=', now()->startOfMonth())->sum('gateway_fee'),
                        'estimated' => Payment::whereIn('status', ['paid', 'refunded'])->where('gateway_fee_source', 'estimate')->count(),
                    ],
        ]);
    }

    /** Admin sudah mengembalikan dana secara manual (transfer bank atau lewat dashboard Midtrans). */
    public function manualRefunded(Request $request, Payment $payment)
    {
        abort_unless($payment->refund_status === 'manual_pending', 404);

        $data = $request->validate(['reference' => ['required', 'string', 'max:100']]);

        DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status'           => 'refunded',
                'refund_status'    => 'manual_done',
                'refund_reference' => $data['reference'],
                'refunded_at'      => now(),
            ]);

            Booking::whereKey($payment->booking_id)->update(['refund_reference' => $data['reference']]);
        });

        return back()->with('success', __('ui.refund_marked_done'));
    }

        /** Admin mengoreksi biaya gateway dengan angka aktual dari laporan Midtrans. */
    public function correctFee(Request $request, Payment $payment)
    {
        abort_unless(in_array($payment->status, ['paid', 'refunded'], true), 404);

        $data = $request->validate([
            'gateway_fee' => ['required', 'integer', 'min:0', 'max:' . (int) $payment->amount],
        ]);

        $payment->update(['gateway_fee' => $data['gateway_fee'], 'gateway_fee_source' => 'actual']);

        return back()->with('success', __('ui.gateway_fee_saved'));
    }
}