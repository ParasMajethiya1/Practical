<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        "merchant_id",
        "balance",
        "held_amount",
        "currency",
    ];

    protected $casts = [
        "balance" => "decimal:2",
        "held_amount" => "decimal:2",
    ];

    protected $appends = [
        "available_balance",
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Funds the merchant can actually spend right now: the ledger balance
     * minus whatever is currently reserved (on hold) against PENDING
     * payouts.
     */
    public function getAvailableBalanceAttribute(): string
    {
        return bcsub((string) $this->balance, (string) $this->held_amount, 2);
    }
}
