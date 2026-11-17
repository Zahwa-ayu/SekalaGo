<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    protected $fillable = [
        'company_id', 'type', 'amount', 'balance_after', 'reference', 'note', 'created_by',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    protected static function booted(): void
    {
        // Buku besar hanya bertambah: baris yang sudah ada tidak boleh diubah atau dihapus
        static::updating(fn () => throw new \LogicException('Mutasi saldo tidak boleh diubah.'));
        static::deleting(fn () => throw new \LogicException('Mutasi saldo tidak boleh dihapus.'));
    }
}