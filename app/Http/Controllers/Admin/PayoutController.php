<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PayoutController extends Controller
{
    public function __construct(private PayoutService $payouts)
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(['pending', 'paid', 'cancelled'])]]);

        $rows = $this->payouts->overview();

        $history = Payout::query()
            ->with('partner')
            ->when($data['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payouts.index', [
            'rows'    => $rows,
            'history' => $history,
            'status'  => $data['status'] ?? null,
            'totals'  => [
                'eligible' => $rows->sum('eligible'),
                'waiting'  => $rows->sum('waiting'),
                'pending'  => (int) Payout::where('status', 'pending')->sum('total_amount'),
                'paid'     => (int) Payout::where('status', 'paid')->where('paid_at', '>=', now()->startOfMonth())->sum('total_amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['partner_id' => ['required', 'integer', 'exists:partners,id']]);

        $payout = $this->payouts->create(Partner::findOrFail($data['partner_id']), $request->user());

        return redirect()->route('admin.payouts.show', $payout)->with('success', __('ui.payout_created'));
    }

    public function generate(Request $request)
    {
        $result = $this->payouts->generateDue($request->user());

        if ($result['created'] === 0) {
            return back()->with('error', __('ui.payout_none_due'));
        }

        return back()->with('success', __('ui.payout_generated', [
            'count'  => $result['created'],
            'amount' => format_price($result['total'], 'IDR'),
        ]));
    }

    public function show(Payout $payout)
    {
        $payout->load('partner', 'creator', 'payer');

        return view('admin.payouts.show', [
            'payout'   => $payout,
            'bookings' => $payout->bookings()->orderBy('check_out')->get(),
        ]);
    }

    public function markPaid(Request $request, Payout $payout)
    {
        $data = $request->validate([
            'transfer_reference' => ['required', 'string', 'max:100'],
            'paid_on'            => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        // Tanggal hari ini memakai jam sekarang; tanggal lain dicatat pukul 12.00
        $paidAt = isset($data['paid_on']) && ! Carbon::parse($data['paid_on'])->isToday()
            ? Carbon::parse($data['paid_on'])->setTime(12, 0)
            : now();

        $this->payouts->markPaid($payout, $request->user(), $data['transfer_reference'], $paidAt);

        return back()->with('success', __('ui.payout_marked_paid'));
    }

    public function cancel(Request $request, Payout $payout)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->payouts->cancel($payout, $request->user(), $data['reason']);

        return redirect()->route('admin.payouts.index')->with('success', __('ui.payout_cancelled'));
    }

    /** Rincian pesanan dalam satu pencairan, untuk arsip dan pencocokan dengan mitra. */
    public function csv(Payout $payout)
    {
        $bookings = $payout->bookings()->orderBy('check_out')->get();

        return response()->streamDownload(function () use ($payout, $bookings) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                __('ui.booking_code'), __('ui.product'), __('ui.item_name'),
                __('ui.period_start'), __('ui.period_end'), 'GMV (IDR)', __('ui.kpi_commission') . ' (IDR)', __('ui.csv_vendor') . ' (IDR)',
            ], ';', '"', '\\');

            foreach ($bookings as $booking) {
                fputcsv($out, [
                    $booking->booking_code,
                    $booking->productLabel(),
                    $booking->hotel_name . ' / ' . $booking->room_name,
                    $booking->check_in->format('Y-m-d'),
                    $booking->check_out->format('Y-m-d'),
                    $booking->total_price,
                    $booking->platform_fee,
                    $booking->vendor_revenue,
                ], ';', '"', '\\');
            }

            fputcsv($out, ['', '', '', '', 'TOTAL', $bookings->sum('total_price'), $payout->platform_fee_total, $payout->total_amount], ';', '"', '\\');

            fclose($out);
        }, $payout->code . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}