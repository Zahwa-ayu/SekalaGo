<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\Concerns\LogsActivity;

class Booking extends Model
{
    use LogsActivity;

    protected $fillable = [
        'booking_code', 'user_id', 'partner_id', 'company_id', 'product_type', 'hotel_id', 'room_type_id',
        'hotel_name', 'room_name', 'check_in', 'check_out', 'nights', 'guests', 'guest_name', 'department',
        'price_per_night', 'total_price', 'platform_fee', 'vendor_revenue', 'policy_limit',
        'currency_code', 'exchange_rate', 'total_price_display',
        'status', 'payment_status', 'payment_method', 'payment_reference', 'expires_at', 'paid_at',
        'free_cancel_days', 'cancelled_at', 'cancelled_by', 'cancellation_reason',
        'refund_amount', 'refunded_at', 'refund_reference',
        'decided_by', 'decided_at', 'decision_note', 'approval_reason', 'vehicle_id', 'with_driver', 
        'driver_fee_per_day', 'pickup_location', 'due_at', 'late_grace_minutes', 'late_fee_percent',
        'picked_up_at', 'returned_at', 'late_minutes', 'late_fee_computed', 'late_fee',
    ];

    protected function casts(): array
    {
        return [
            'check_in'            => 'date',
            'check_out'           => 'date',
            'expires_at'          => 'datetime',
            'paid_at'             => 'datetime',
            'cancelled_at'        => 'datetime',
            'refunded_at'         => 'datetime',
            'decided_at'          => 'datetime',
            'exchange_rate'       => 'float',
            'total_price_display' => 'float',
            'free_cancel_days'    => 'integer',
            'with_driver' => 'boolean',
            'due_at'       => 'datetime',
            'picked_up_at' => 'datetime',
            'returned_at'  => 'datetime',
        ];
    }

    public function user()     { return $this->belongsTo(User::class); }
    public function partner()  { return $this->belongsTo(Partner::class); }
    public function company()  { return $this->belongsTo(Company::class); }
    public function hotel()    { return $this->belongsTo(Hotel::class); }
    public function roomType() { return $this->belongsTo(RoomType::class); }

    public function isCorporate(): bool
    {
        return $this->company_id !== null;
    }

    /** Total dalam mata uang yang dipilih pemesan saat memesan (kurs terkunci). */
    public function displayTotal(): string
    {
        $currency = Currency::find($this->currency_code);
        $decimals = $currency?->decimal_places ?? 0;
        [$decimal, $thousand] = app()->getLocale() === 'id' ? [',', '.'] : ['.', ','];

        return ($currency?->symbol ?? $this->currency_code) . ' '
            . number_format($this->total_price_display, $decimals, $decimal, $thousand);
    }

    /** Pembatalan gratis berlaku sampai tanggal ini (check-in dikurangi batas hari dari MOU). */
    public function freeCancelDeadline(): Carbon
    {
        return $this->check_in->copy()->subDays($this->free_cancel_days)->startOfDay();
    }

    public function canBeCancelledByCustomer(): bool
    {
        if (in_array($this->status, ['pending_payment', 'pending_approval'], true)) {
            return true;
        }

        return $this->status === 'confirmed' && today()->lte($this->freeCancelDeadline());
    }

    public function canBeCancelledByAdmin(): bool
    {
        return $this->status === 'confirmed' && today()->lte($this->check_in) && $this->picked_up_at === null;
    }

    protected function logAttributes(): array
    {
        return ['status', 'payment_status', 'total_price', 'refund_amount',
                'cancelled_by', 'cancellation_reason', 'decision_note'];
    }

    protected function logEvents(): array { return ['created', 'updated']; }

    public function logLabel(): string { return $this->booking_code; }

    public function vehicle() { return $this->belongsTo(Vehicle::class); }

    public function isCar(): bool
    {
        return $this->product_type === 'car';
    }

    /** "3 malam" untuk hotel, "3 hari" untuk rental mobil. */
    public function durationLabel(): string
    {
        return $this->isCar()
            ? __('ui.for_days', ['count' => $this->nights])
            : __('ui.for_nights', ['count' => $this->nights]);
    }

    public function productIcon(): string
    {
        return $this->isCar() ? 'fa-car-side' : 'fa-hotel';
    }

    public function productLabel(): string
    {
        return $this->isCar() ? __('ui.tab_cars') : __('ui.tab_hotels');
    }

    /**
 * Tahap sewa mobil: upcoming, ongoing, overdue, atau returned.
 * Null untuk hotel atau pesanan yang belum terkonfirmasi.
 */
public function rentalStage(): ?string
{
    if (! $this->isCar() || $this->status !== 'confirmed') {
        return null;
    }

    if ($this->returned_at) {
        return 'returned';
    }

    if (! $this->picked_up_at) {
        return 'upcoming';
    }

    return $this->due_at && $this->due_at->isPast() ? 'overdue' : 'ongoing';
}

/**
 * Hari pertama unit ini kembali bebas (batas akhir eksklusif).
 * Mobil yang sudah diambil tapi belum kembali setelah tanggal kembali tetap memakai unit sampai hari ini.
 */
public function occupiedUntil(): Carbon
{
    if ($this->vehicle_id && $this->picked_up_at && ! $this->returned_at && $this->check_out->lte(today())) {
        return today()->addDay();
    }

    return $this->check_out->copy();
}

/** Pesanan mobil yang belum diambil tapi unitnya sedang tertahan oleh mobil yang terlambat. */
public function isAtRisk(): bool
{
    return app(\App\Services\RentalService::class)->isAtRisk($this);
}

public function extensions()
{
    return $this->hasMany(BookingExtension::class)->orderBy('id');
}

public function canBeExtended(): bool
{
    return app(\App\Services\ExtensionService::class)->canExtend($this);
}
}
