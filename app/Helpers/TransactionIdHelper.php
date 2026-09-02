<?php

namespace App\Helpers;

use App\Models\Payin;
use App\Models\Payout;
use Illuminate\Support\Str;

class TransactionIdHelper
{
    /**
     * Generate a guaranteed-unique transaction id such as
     * PIN20260902A1B2C3D4E5 (Pay-in) or POT20260902A1B2C3D4E5 (Payout).
     */
    public static function generate(string $prefix): string
    {
        do {
            $candidate = strtoupper($prefix) . now()->format("Ymd") . strtoupper(Str::random(10));
        } while (self::exists($candidate));

        return $candidate;
    }

    protected static function exists(string $transactionId): bool
    {
        return Payin::where("transaction_id", $transactionId)->exists()
            || Payout::where("transaction_id", $transactionId)->exists();
    }
}
