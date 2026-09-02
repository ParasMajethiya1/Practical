<?php

namespace App\Services;

use App\Helpers\TransactionIdHelper;
use App\Models\Merchant;
use App\Models\Payin;
use Illuminate\Support\Facades\DB;

class PayinService
{
    /**
     * Initiate a new pay-in for a merchant.
     * - Generates a unique transaction id
     * - Persists the record with PENDING status
     * - Logs the full request/payment details
     */
    public function initiate(Merchant $merchant, array $data): Payin
    {
        return DB::transaction(function () use ($merchant, $data) {
            $payin = Payin::create([
                "transaction_id" => TransactionIdHelper::generate("PIN"),
                "merchant_id" => $merchant->id,
                "amount" => $data["amount"],
                "currency" => $data["currency"] ?? "INR",
                "status" => Payin::STATUS_PENDING,
                "payment_method" => $data["payment_method"] ?? null,
                "customer_details" => [
                    "name" => $data["customer_name"] ?? null,
                    "email" => $data["customer_email"] ?? null,
                    "phone" => $data["customer_phone"] ?? null,
                ],
                "remarks" => $data["remarks"] ?? null,
                "meta" => $data["meta"] ?? null,
            ]);

            PaymentLogger::log(
                $payin,
                "INITIATED",
                Payin::STATUS_PENDING,
                "Pay-in initiated for merchant {$merchant->name}",
                ["request" => $data]
            );

            return $payin;
        });
    }

    public function listFor(Merchant $merchant, array $filters = [])
    {
        return Payin::query()
            ->forMerchant($merchant->id)
            ->status($filters["status"] ?? null)
            ->betweenDates($filters["date_from"] ?? null, $filters["date_to"] ?? null)
            ->latest()
            ->paginate($filters["per_page"] ?? 15);
    }
}
