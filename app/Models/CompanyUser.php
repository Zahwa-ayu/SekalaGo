<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CompanyUser extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'full_name', 'role_in_company', 'department', 'position_id',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function company()  { return $this->belongsTo(Company::class); }
    public function position() { return $this->belongsTo(CompanyPosition::class, 'position_id'); }

    public function isFinance(): bool
    {
        return $this->role_in_company === 'admin_finance';
    }

    public function displayName(): string
    {
        return $this->full_name ?: $this->user->email;
    }

    /** Limit per pemesanan: dari jabatan, atau limit default perusahaan jika belum punya jabatan. */
    public function bookingLimit(): int
    {
        return (int) ($this->position?->booking_limit ?? $this->company->travel_policy_limit);
    }

    /** Anggaran bulanan: dari jabatan, atau default perusahaan. null = tanpa batas bulanan. */
    public function monthlyBudget(): ?int
    {
        $budget = $this->position?->monthly_budget ?? $this->company->default_monthly_budget;

        return $budget !== null ? (int) $budget : null;
    }

    /** Belanja karyawan pada bulan tertentu (pesanan terkonfirmasi dan yang menunggu persetujuan). */
    public function monthlySpent(?Carbon $month = null): int
    {
        $month = $month ?? now();

        return (int) Booking::where('user_id', $this->user_id)
            ->whereIn('status', ['confirmed', 'pending_approval'])
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('total_price');
    }

    /**
     * Nilai satu pemesanan terhadap aturan perusahaan.
     *
     * @return array{limit: int, budget: ?int, spent: int, over_limit: bool, over_budget: bool, needs_approval: bool, reason: ?string}
     */
    public function evaluatePolicy(int $total): array
    {
        $limit  = $this->bookingLimit();
        $budget = $this->monthlyBudget();
        $spent  = $this->monthlySpent();

        $overLimit  = $total > $limit;
        $overBudget = $budget !== null && ($spent + $total) > $budget;

        return [
            'limit'          => $limit,
            'budget'         => $budget,
            'spent'          => $spent,
            'over_limit'     => $overLimit,
            'over_budget'    => $overBudget,
            // Finance bebas dari persetujuan (sesuai PDF perancangan)
            'needs_approval' => ($overLimit || $overBudget) && ! $this->isFinance(),
            'reason'         => match (true) {
                $overLimit && $overBudget => 'limit_budget',
                $overLimit                => 'limit',
                $overBudget               => 'budget',
                default                   => null,
            },
        ];
    }
}