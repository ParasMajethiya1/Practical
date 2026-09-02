<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    public const STATUS_PENDING = "PENDING";
    public const STATUS_SUCCESS = "SUCCESS";
    public const STATUS_FAILED = "FAILED";

    protected $fillable = [
        "transaction_id",
        "merchant_id",
        "amount",
        "currency",
        "status",
        "payout_method",
        "beneficiary_details",
        "remarks",
        "failure_reason",
        "meta",
        "processed_at",
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "beneficiary_details" => "array",
        "meta" => "array",
        "processed_at" => "datetime",
    ];

    public function getRouteKeyName(): string
    {
        return "transaction_id";
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where("status", $status) : $query;
    }

    public function scopeForMerchant($query, ?int $merchantId)
    {
        return $merchantId ? $query->where("merchant_id", $merchantId) : $query;
    }

    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate("created_at", ">=", $from);
        }
        if ($to) {
            $query->whereDate("created_at", "<=", $to);
        }
        return $query;
    }
}
