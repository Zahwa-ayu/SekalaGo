<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Hotel;
use App\Models\Partner;
use App\Models\PartnerAgreement;
use App\Models\Payout;
use App\Models\Vehicle;
use App\Services\Notifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class NotificationEvents implements ShouldHandleEventsAfterCommit
{
    public function __construct(private Notifier $notifier)
    {
    }

    public function created(Model $model): void
    {
        match (true) {
            $model instanceof Booking => $this->bookingCreated($model),
            $model instanceof Partner => $this->notifier->to(
                $this->notifier->admins(), 'partner_registered', ['company' => $model->company_name],
                route('admin.partners.show', $model), 'fa-handshake'
            ),
            $model instanceof Company => $this->companyCreated($model),
            $model instanceof Payout  => $this->notifier->to(
                $model->partner->user, 'payout_created',
                ['code' => $model->code, 'amount' => $this->idr($model->total_amount), 'bank' => $model->bankLabel()],
                route('partner.payouts.index'), 'fa-wallet'
            ),
            default => null,
        };
    }

    public function updated(Model $model): void
    {
        match (true) {
            $model instanceof Booking          => $this->bookingUpdated($model),
            $model instanceof Partner          => $this->partnerUpdated($model),
            $model instanceof PartnerAgreement => $this->agreementUpdated($model),
            $model instanceof Company          => $this->companyUpdated($model),
            $model instanceof Hotel, $model instanceof Vehicle => $this->productUpdated($model),
            $model instanceof Payout           => $this->payoutUpdated($model),
            default => null,
        };
    }

    // -------------------------------------------------------------- pesanan

    private function bookingCreated(Booking $booking): void
    {
        if ($booking->status === 'confirmed') {
            $this->confirmed($booking, 'booking_confirmed');
        } elseif ($booking->status === 'pending_approval' && $booking->company_id) {
            $this->notifier->to(
                $this->notifier->financeOf($booking->company_id), 'approval_requested',
                [
                    'code'     => $booking->booking_code,
                    'employee' => $booking->contact_name ?: $booking->guest_name,
                    'name'     => $booking->hotel_name,
                    'period'   => $this->period($booking),
                    'amount'   => $this->idr($booking->total_price),
                    'reason'   => __('notif.reason_' . ($booking->approval_reason ?: 'limit')),
                ],
                route('corporate.bookings.index'), 'fa-clipboard-check'
            );
        }
    }

    private function bookingUpdated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            $from = $booking->getOriginal('status');

            match ($booking->status) {
                'confirmed' => $this->confirmed($booking, $from === 'pending_approval' ? 'approval_approved' : 'booking_confirmed'),
                'cancelled' => $this->cancelled($booking),
                'rejected'  => $this->notifier->to($booking->user, 'approval_rejected', [
                    'code' => $booking->booking_code, 'name' => $booking->hotel_name, 'note' => $booking->decision_note ?: '-',
                ], route('bookings.show', $booking), 'fa-circle-xmark'),
                'expired'   => $this->notifier->to($booking->user, 'booking_expired', [
                    'code' => $booking->booking_code, 'name' => $booking->hotel_name,
                ], route('bookings.show', $booking), 'fa-hourglass-end'),
                default     => null,
            };
        }

        if ($booking->wasChanged('no_show_at') && $booking->no_show_at) {
            $this->notifier->to($booking->user, 'no_show_recorded', [
                'code' => $booking->booking_code, 'name' => $booking->hotel_name,
            ], route('bookings.show', $booking), 'fa-user-slash');
        }

        if ($booking->wasChanged('returned_at') && $booking->returned_at && $booking->late_fee > 0) {
            $this->notifier->to($booking->user, 'late_fee_recorded', [
                'code' => $booking->booking_code, 'name' => $booking->hotel_name, 'amount' => $this->idr($booking->late_fee),
            ], route('bookings.show', $booking), 'fa-clock');
        }

        // Perpanjangan sewa: tanggal kembali bertambah
        if ($booking->vehicle_id && $booking->wasChanged('check_out')
            && $booking->check_out->gt(Carbon::parse($booking->getOriginal('check_out')))) {
            $this->notifier->to($booking->partner->user, 'extension_done', [
                'code'   => $booking->booking_code,
                'name'   => $booking->hotel_name,
                'date'   => $booking->check_out->format('d/m/Y'),
                'amount' => $this->idr($booking->total_price - (int) $booking->getOriginal('total_price')),
            ], route('partner.rentals.index'), 'fa-calendar-plus');
        }
    }

    /** Pelanggan: pesanan terkonfirmasi. Mitra: ada pesanan baru. */
    private function confirmed(Booking $booking, string $bookerEvent): void
    {
        $this->notifier->to($booking->user, $bookerEvent, [
            'code'   => $booking->booking_code,
            'name'   => $booking->hotel_name,
            'period' => $this->period($booking),
            'amount' => $this->idr($booking->total_price),
        ], route('bookings.show', $booking), 'fa-circle-check');

        $this->notifier->to($booking->partner->user, 'new_booking', [
            'code'   => $booking->booking_code,
            'guest'  => $booking->guest_name,
            'name'   => $booking->hotel_name,
            'period' => $this->period($booking),
            'amount' => $this->idr($booking->vendor_revenue),
        ], route('partner.bookings.index'), 'fa-receipt');
    }

    private function cancelled(Booking $booking): void
    {
        $by     = $booking->cancelled_by ?: 'customer';
        $byText = __('notif.by_' . $by);

        $this->notifier->to($booking->user, 'booking_cancelled', [
            'code'   => $booking->booking_code,
            'name'   => $booking->hotel_name,
            'by'     => $byText,
            'refund' => $booking->refund_amount > 0 ? __('notif.refund_line', ['amount' => $this->idr($booking->refund_amount)]) : '',
        ], route('bookings.show', $booking), 'fa-ban');

        if ($by !== 'partner') {
            $this->notifier->to($booking->partner->user, 'booking_cancelled_partner', [
                'code' => $booking->booking_code, 'name' => $booking->hotel_name, 'guest' => $booking->guest_name, 'by' => $byText,
            ], route('partner.bookings.index', ['status' => 'cancelled']), 'fa-ban');
        }
    }

    // ---------------------------------------------------------- mitra dan MOU

    private function partnerUpdated(Partner $partner): void
    {
        if (! $partner->wasChanged('verification_status')) {
            return;
        }

        // Mitra yang belum terverifikasi belum bisa masuk: kabarnya selalu lewat email
        match ($partner->verification_status) {
            'verified' => $this->notifier->to($partner->user, 'partner_verified', [],
                route('partner.agreement.show'), 'fa-circle-check', true),
            'rejected' => $this->notifier->to($partner->user, 'partner_rejected',
                ['note' => $partner->verification_note ?: '-'], route('login'), 'fa-circle-xmark', true),
            default => null,
        };
    }

    private function agreementUpdated(PartnerAgreement $agreement): void
    {
        if ($agreement->wasChanged('status') && $agreement->status === 'signed') {
            $this->notifier->to($this->notifier->admins(), 'mou_signed', [
                'company'    => $agreement->partner->company_name,
                'commission' => rtrim(rtrim(number_format($agreement->commission_rate * 100, 2, '.', ''), '0'), '.'),
            ], route('admin.partners.show', $agreement->partner_id), 'fa-file-signature');
        }
    }

    // ------------------------------------------------------------ perusahaan

    private function companyCreated(Company $company): void
    {
        if ($company->source === 'self' && $company->verification_status === 'pending') {
            $this->notifier->to($this->notifier->admins(), 'company_registered', ['company' => $company->company_name],
                route('admin.companies.show', $company), 'fa-building');
        }
    }

    private function companyUpdated(Company $company): void
    {
        if (! $company->wasChanged('verification_status') || ! in_array($company->verification_status, ['verified', 'rejected'], true)) {
            return;
        }

        $this->notifier->to(
            $this->notifier->financeOf($company->id),
            $company->verification_status === 'verified' ? 'company_verified' : 'company_rejected',
            ['company' => $company->company_name, 'note' => $company->verification_note ?: '-'],
            route('login'), $company->verification_status === 'verified' ? 'fa-circle-check' : 'fa-circle-xmark', true
        );
    }

    // --------------------------------------------------------- hotel, armada

    private function productUpdated(Hotel|Vehicle $product): void
    {
        if (! $product->wasChanged('status') || ! in_array($product->status, ['approved', 'rejected'], true)) {
            return;
        }

        $isHotel = $product instanceof Hotel;
        $params  = [
            'type'   => __($isHotel ? 'notif.type_hotel' : 'notif.type_vehicle'),
            'name'   => $product->name,
            'reason' => $product->rejection_reason ?? '-',
        ];

        $this->notifier->to(
            $product->partner->user,
            $product->status === 'approved' ? 'product_approved' : 'product_rejected',
            $params,
            route($isHotel ? 'partner.hotels.index' : 'partner.vehicles.index'),
            $product->status === 'approved' ? 'fa-circle-check' : 'fa-circle-xmark'
        );
    }

    // ------------------------------------------------------------- pencairan

    private function payoutUpdated(Payout $payout): void
    {
        if ($payout->wasChanged('status') && $payout->status === 'paid') {
            $this->notifier->to($payout->partner->user, 'payout_paid', [
                'code' => $payout->code, 'amount' => $this->idr($payout->total_amount),
            ], route('partner.payouts.index'), 'fa-money-bill-transfer');
        }
    }

    // ---------------------------------------------------------------- helper

    private function idr(int|float $amount): string
    {
        return format_price($amount, 'IDR');
    }

    private function period(Booking $booking): string
    {
        return $booking->check_in->format('d/m/Y') . ' - ' . $booking->check_out->format('d/m/Y');
    }
}