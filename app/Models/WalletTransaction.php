<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    public const TYPE_CREDIT = "CREDIT";
    public const TYPE_DEBIT = "DEBIT";
    public const TYPE_HOLD = "HOLD";
    public const TYPE_RELEASE = "RELEASE";

    protected $fillable = [
        "wallet_id",
        "merchant_id",
        "type",
        "amount",
        "balance_before",
        "balance_after",
        "reference_type",
        "reference_id",
        "description",
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "balance_before" => "decimal:2",
        "balance_after" => "decimal:2",
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
