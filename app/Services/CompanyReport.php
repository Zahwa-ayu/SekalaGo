<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Company;
use Illuminate\Support\Carbon;

class CompanyReport
{
    /** Laporan satu bulan: pesanan yang dibuat pada bulan itu. Belanja = pesanan terkonfirmasi. */
    public function build(Company $company, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $bookings = Booking::where('company_id', $company->id)
            ->whereBetween('created_at', [$start, $end])
            ->with('user.companyUser.position', 'user.companyUser.company')
            ->orderBy('created_at')
            ->get();

        $confirmed = $bookings->where('status', 'confirmed')->values();
        $total     = (int) $confirmed->sum('total_price');

        $summary = [
            'orders'    => $confirmed->count(),
            'total'     => $total,
            'average'   => $confirmed->count() ? (int) round($total / $confirmed->count()) : 0,
            'nights'    => (int) $confirmed->sum('nights'),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'refunded'  => (int) $bookings->where('status', 'cancelled')->sum('refund_amount'),
            'rejected'  => $bookings->where('status', 'rejected')->count(),
            'pending'   => $bookings->where('status', 'pending_approval')->count(),
        ];

        $byDepartment = $confirmed
            ->groupBy(fn ($booking) => $booking->department ?: '-')
            ->map(fn ($group, $department) => [
                'department' => $department,
                'orders'     => $group->count(),
                'total'      => (int) $group->sum('total_price'),
            ])
            ->sortByDesc('total')
            ->values();

        $byEmployee = $confirmed
            ->groupBy('user_id')
            ->map(function ($group) {
                $first  = $group->first();
                $member = $first->user->companyUser;
                $budget = $member?->monthlyBudget();
                $sum    = (int) $group->sum('total_price');

                return [
                    'name'         => $member?->displayName() ?? $first->user->email,
                    'department'   => $member?->department ?? $first->department ?? '-',
                    'position'     => $member?->position?->name ?? '-',
                    'orders'       => $group->count(),
                    'total'        => $sum,
                    'budget'       => $budget,
                    'used_percent' => $budget ? (int) round($sum / $budget * 100) : null,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'company'      => $company,
            'month'        => $start,
            'summary'      => $summary,
            'byDepartment' => $byDepartment,
            'byEmployee'   => $byEmployee,
            'rows'         => $confirmed,
        ];
    }
}