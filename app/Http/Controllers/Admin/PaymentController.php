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
}