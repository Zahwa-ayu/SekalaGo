<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IssueService
{
    public const CATEGORIES = ['not_as_described', 'not_available', 'service_issue', 'refund_problem', 'other'];
    public const WINDOW_DAYS = 60;
    public const MAX_PER_BOOKING = 5;

    public function __construct(private Notifier $notifier)
    {
    }

    /**
     * Kategori yang boleh dipilih pemilik pesanan; kosong berarti belum bisa melapor.
     * Hanya pemesan sendiri, hanya pesanan yang pernah dibayar, dan dalam batas waktu.
     *
     * @return list<string>
     */
    public function allowedCategories(Booking $booking, ?User $user): array
    {
        if (! $user || $booking->user_id !== $user->id) {
            return [];
        }

        if (! in_array($booking->status, ['confirmed', 'cancelled'], true)) {
            return [];
        }

        $latest = $booking->status === 'cancelled'
            ? $booking->cancelled_at
            : $booking->check_out->copy()->endOfDay();

        if ($latest && now()->gt($latest->copy()->addDays(self::WINDOW_DAYS))) {
            return [];
        }

        if ($booking->status === 'cancelled') {
            // Pesanan batal yang belum pernah dibayar tidak punya hal untuk dilaporkan
            return in_array($booking->payment_status, ['paid', 'refunded'], true) ? ['refund_problem', 'other'] : [];
        }

        // Laporan layanan baru masuk akal setelah tanggal mulai
        return $booking->check_in->lte(today())
            ? ['not_as_described', 'not_available', 'service_issue', 'other']
            : ['other'];
    }

    public function create(Booking $booking, User $user, string $category, string $description, ?UploadedFile $evidence = null): IssueReport
    {
        if (! in_array($category, $this->allowedCategories($booking, $user), true)) {
            throw ValidationException::withMessages(['category' => __('ui.issue_not_allowed')]);
        }

        $existing = IssueReport::where('booking_id', $booking->id);

        if ((clone $existing)->where('category', $category)->whereIn('status', ['open', 'in_review'])->exists()) {
            throw ValidationException::withMessages(['category' => __('ui.issue_duplicate')]);
        }

        if ((clone $existing)->count() >= self::MAX_PER_BOOKING) {
            throw ValidationException::withMessages(['category' => __('ui.issue_too_many', ['max' => self::MAX_PER_BOOKING])]);
        }

        $path = $evidence?->store("issue-evidence/{$booking->id}", 'local');

        try {
            $report = IssueReport::create([
                'code'          => $this->newCode(),
                'booking_id'    => $booking->id,
                'user_id'       => $user->id,
                'partner_id'    => $booking->partner_id,
                'category'      => $category,
                'description'   => $description,
                'evidence_path' => $path,
                'status'        => 'open',
            ]);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            throw $e;
        }

        $params = ['code' => $report->code, 'booking' => $booking->booking_code, 'name' => $booking->hotel_name];

        $this->notifier->to($this->notifier->admins('operations'), 'issue_reported', $params, route('admin.issues.index'), 'fa-triangle-exclamation');
        $this->notifier->to($booking->partner->user, 'issue_for_partner', $params, route('partner.issues.index'), 'fa-triangle-exclamation');

        return $report;
    }

    /** Admin mengubah status dan menjawab pelanggan. */
    public function handle(IssueReport $report, User $admin, string $status, ?string $resolution): IssueReport
    {
        $closing = in_array($status, ['resolved', 'rejected'], true);

        if ($closing && blank($resolution)) {
            throw ValidationException::withMessages(['resolution' => __('ui.issue_resolution_required')]);
        }

        $report->update([
            'status'      => $status,
            'resolution'  => $resolution ?: $report->resolution,
            'handled_by'  => $admin->id,
            'resolved_at' => $closing ? now() : null,
        ]);

        $this->notifier->to($report->user, 'issue_updated', [
            'code'   => $report->code,
            'status' => __('ui.issue_status_' . $status),
        ], route('issues.index'), 'fa-life-ring');

        return $report;
    }

    public function respond(IssueReport $report, int $partnerId, string $text): IssueReport
    {
        abort_unless($report->partner_id === $partnerId, 404);

        $report->update(['partner_response' => $text, 'partner_responded_at' => now()]);

        return $report;
    }

    private function newCode(): string
    {
        do {
            $code = 'IR-' . now()->format('ym') . '-' . strtoupper(Str::random(5));
        } while (IssueReport::where('code', $code)->exists());

        return $code;
    }
}