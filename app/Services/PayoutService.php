<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Helpers\TransactionIdHelper;
use App\Models\Merchant;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Initiate a new payout for a merchant.
     * A soft balance check is performed up-front so obviously invalid
     * payouts are rejected immediately, in addition to the hard,
     * lock-protected check performed again at processing time in
     * PaymentProcessingService (balances can change between initiation
     * and processing, so both checks are necessary).
     *
     * @throws InsufficientBalanceException
     */
    public function initiate(Merchant $merchant, array $data): Payout
    {
        if (! $this->walletService->hasSufficientBalance($merchant->id, (string) $data["amount"])) {
            throw new InsufficientBalanceException(
                "Merchant does not have sufficient wallet balance to initiate this payout."
            );
        }

        return DB::transaction(function () use ($merchant, $data) {
            $payout = Payout::create([
                "transaction_id" => TransactionIdHelper::generate("POT"),
                "merchant_id" => $merchant->id,
                "amount" => $data["amount"],
                "currency" => $data["currency"] ?? "INR",
                "status" => Payout::STATUS_PENDING,
                "payout_method" => $data["payout_method"] ?? null,
                "beneficiary_details" => [
                    "name" => $data["beneficiary_name"] ?? null,
                    "account_number" => $data["beneficiary_account_number"] ?? null,
                    "ifsc" => $data["beneficiary_ifsc"] ?? null,
                    "bank_name" => $data["beneficiary_bank_name"] ?? null,
                ],
                "remarks" => $data["remarks"] ?? null,
                "meta" => $data["meta"] ?? null,
            ]);

            PaymentLogger::log(
                $payout,
                "INITIATED",
                Payout::STATUS_PENDING,
                "Payout initiated for merchant {$merchant->name}",
                ["request" => $data]
            );

            return $payout;
        });
    }

    public function listFor(Merchant $merchant, array $filters = [])
    {
        return Payout::query()
            ->forMerchant($merchant->id)
            ->status($filters["status"] ?? null)
            ->betweenDates($filters["date_from"] ?? null, $filters["date_to"] ?? null)
            ->latest()
            ->paginate($filters["per_page"] ?? 15);
    }
}
