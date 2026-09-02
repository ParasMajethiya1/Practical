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
     *
     * A soft balance check is performed up-front so obviously invalid
     * payouts are rejected immediately, in addition to the hard,
     * lock-protected check performed again inside holdForPayout()
     * (balances can change between initiation and the lock being
     * acquired, so both checks are necessary).
     *
     * On success, the payout amount is immediately placed ON HOLD via
     * WalletService::holdForPayout() - it is reserved out of the
     * merchant's available balance right away, so it cannot be spent
     * twice by another payout while this one is still PENDING. The hold
     * is only released back to the merchant (on FAILED) or converted
     * into a real debit (on SUCCESS) once PaymentProcessingService
     * resolves the payout - see WalletService for the full lifecycle.
     *
     * @throws InsufficientBalanceException
     */
    public function initiate(Merchant $merchant, array $data): Payout
    {
        if (! $this->walletService->hasSufficientBalance($merchant->id, (string) $data["amount"])) {
            throw new InsufficientBalanceException(
                "Merchant does not have sufficient available wallet balance to initiate this payout."
            );
        }

        $holdFailure = null;

        $payout = DB::transaction(function () use ($merchant, $data, &$holdFailure) {
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

            try {
                $this->walletService->holdForPayout($payout);
            } catch (InsufficientBalanceException $e) {
                // Balance was consumed by a concurrent payout between the
                // soft check above and the row lock inside holdForPayout().
                // Fail the payout outright (and keep this whole DB
                // transaction committing normally) instead of leaving it
                // PENDING with no funds actually reserved for it.
                $payout->status = Payout::STATUS_FAILED;
                $payout->failure_reason = $e->getMessage();
                $payout->processed_at = now();
                $payout->save();

                PaymentLogger::error($payout, "HOLD_FAILED", $e->getMessage());

                $holdFailure = $e;

                return $payout;
            }

            PaymentLogger::log(
                $payout,
                "INITIATED",
                Payout::STATUS_PENDING,
                "Payout initiated for merchant {$merchant->name}; {$payout->amount} {$payout->currency} placed on hold",
                ["request" => $data]
            );

            return $payout;
        });

        // Re-thrown OUTSIDE the DB transaction above so the FAILED status
        // and log entry we just wrote are not rolled back with it.
        if ($holdFailure) {
            throw $holdFailure;
        }

        return $payout;
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
