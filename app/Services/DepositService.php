<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DepositTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositService
{
    /** Tambah saldo (topup, refund, adjustment). */
    public function credit(
        Company $company,
        int $amount,
        string $type = 'topup',
        ?string $note = null,
        ?string $reference = null,
        ?User $by = null,
    ): DepositTransaction {
        return $this->apply($company, abs($amount), $type, $note, $reference, $by);
    }

    /** Kurangi saldo (dipakai pemesanan di 5.2). Gagal jika saldo tidak cukup. */
    public function debit(
        Company $company,
        int $amount,
        ?string $note = null,
        ?string $reference = null,
        ?User $by = null,
    ): DepositTransaction {
        return $this->apply($company, -abs($amount), 'charge', $note, $reference, $by);
    }

    private function apply(Company $company, int $signed, string $type, ?string $note, ?string $reference, ?User $by): DepositTransaction
    {
        return DB::transaction(function () use ($company, $signed, $type, $note, $reference, $by) {
            $locked  = Company::lockForUpdate()->findOrFail($company->id);
            $balance = (int) $locked->deposit_balance + $signed;

            if ($balance < 0) {
                throw ValidationException::withMessages(['deposit' => __('ui.deposit_insufficient')]);
            }

            $locked->update(['deposit_balance' => $balance]);

            return DepositTransaction::create([
                'company_id'    => $locked->id,
                'type'          => $type,
                'amount'        => $signed,
                'balance_after' => $balance,
                'reference'     => $reference,
                'note'          => $note,
                'created_by'    => $by?->id,
            ]);
        });
    }
}