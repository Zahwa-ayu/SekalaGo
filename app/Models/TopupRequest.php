<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\FiltersByDate;

class TopupRequest extends Model
{
    use FiltersByDate;
    
    protected $fillable = [
        'code', 'company_id', 'requested_by', 'platform_bank_account_id', 'amount', 'approved_amount',
        'transfer_date', 'sender_name', 'note', 'proof_path',
        'status', 'decided_by', 'decided_at', 'decision_note',
    ];

    protected function casts(): array
    {
        return ['transfer_date' => 'date', 'decided_at' => 'datetime'];
    }

    public function company()   { return $this->belongsTo(Company::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function account()   { return $this->belongsTo(PlatformBankAccount::class, 'platform_bank_account_id'); }
    public function decider()   { return $this->belongsTo(User::class, 'decided_by'); }
}