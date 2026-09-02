<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Payin;
use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * All wallet balance mutations MUST go through this service.
 *
 * Safety guarantees:
 *  1. Row-level locking (lockForUpdate) on the wallet row prevents two
 *     concurrent processes from reading/writing a stale balance.
 *  2. The `wallet_tx_reference_unique` DB constraint on
 *     (reference_type, reference_id) makes it structurally impossible
 *     to credit/debit/hold/release the wallet twice for the same
 *     Payin/Payout, even if this method is accidentally called twice or
 *     two cron workers race each other.
 *
 * Payout hold/release lifecycle
 * ------------------------------
 * A payout never touches `balance` directly at initiation time. Instead:
 *
 *   1. initiate()  -> holdForPayout()     `held_amount` += amount
 *                                          (balance untouched, but the
 *                                          amount is no longer "available")
 *   2. processed as SUCCESS -> debitForPayout()
 *                                          `held_amount` -= amount
 *                                          `balance`     -= amount
 *                                          (the hold is converted into a
 *                                          real, permanent debit)
 *   2. processed as FAILED  -> releaseHoldForPayout()
 *                                          `held_amount` -= amount
 *                                          (balance untouched - funds
 *                                          become available again)
 *
 * This guarantees the merchant can never spend the same rupee twice
 * across two concurrently-pending payouts, without prematurely debiting
 * money for a payout that might still fail.
 */
class WalletService
{
    public function creditForPayin(Payin $payin): WalletTransaction
    {
        return DB::transaction(function () use ($payin) {
            $wallet = Wallet::where("merchant_id", $payin->merchant_id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new \RuntimeException("Wallet not found for merchant #{$payin->merchant_id}");
            }

            // Idempotency guard - if a wallet transaction already exists for
            // this payin, do not credit again.
            $existing = WalletTransaction::where("reference_type", "payin")
                ->where("reference_id", $payin->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $balanceBefore = $wallet->balance;
            $balanceAfter = bcadd($balanceBefore, $payin->amount, 2);

            $wallet->update(["balance" => $balanceAfter]);

            return WalletTransaction::create([
                "wallet_id" => $wallet->id,
                "merchant_id" => $payin->merchant_id,
                "type" => WalletTransaction::TYPE_CREDIT,
                "amount" => $payin->amount,
                "balance_before" => $balanceBefore,
                "balance_after" => $balanceAfter,
                "reference_type" => "payin",
                "reference_id" => $payin->id,
                "description" => "Credit for successful pay-in {$payin->transaction_id}",
            ]);
        });
    }

    /**
     * Step 1 of the payout lifecycle: reserve the payout amount so it can
     * no longer be spent by another payout, WITHOUT touching the real
     * ledger balance yet. Called from PayoutService::initiate().
     *
     * @throws InsufficientBalanceException
     */
    public function holdForPayout(Payout $payout): WalletTransaction
    {
        return DB::transaction(function () use ($payout) {
            $wallet = Wallet::where("merchant_id", $payout->merchant_id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new \RuntimeException("Wallet not found for merchant #{$payout->merchant_id}");
            }

            // Idempotency guard - if this payout was already put on hold
            // (e.g. a retried request), do not hold the funds again.
            $existing = WalletTransaction::where("reference_type", "payout_hold")
                ->where("reference_id", $payout->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $available = bcsub((string) $wallet->balance, (string) $wallet->held_amount, 2);

            if (bccomp($available, (string) $payout->amount, 2) < 0) {
                throw new InsufficientBalanceException(
                    "Insufficient available wallet balance ({$available}) for payout amount ({$payout->amount})."
                );
            }

            $heldBefore = $wallet->held_amount;
            $heldAfter = bcadd($heldBefore, $payout->amount, 2);

            $wallet->update(["held_amount" => $heldAfter]);

            return WalletTransaction::create([
                "wallet_id" => $wallet->id,
                "merchant_id" => $payout->merchant_id,
                "type" => WalletTransaction::TYPE_HOLD,
                "amount" => $payout->amount,
                "balance_before" => $heldBefore,
                "balance_after" => $heldAfter,
                "reference_type" => "payout_hold",
                "reference_id" => $payout->id,
                "description" => "Amount placed on hold for payout {$payout->transaction_id}",
            ]);
        });
    }

    /**
     * Step 2a (SUCCESS path): converts the existing hold into a permanent
     * debit - `held_amount` and `balance` both drop by the payout amount.
     *
     * @throws InsufficientBalanceException
     */
    public function debitForPayout(Payout $payout): WalletTransaction
    {
        return DB::transaction(function () use ($payout) {
            $wallet = Wallet::where("merchant_id", $payout->merchant_id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new \RuntimeException("Wallet not found for merchant #{$payout->merchant_id}");
            }

            $existing = WalletTransaction::where("reference_type", "payout")
                ->where("reference_id", $payout->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            if (bccomp($wallet->balance, $payout->amount, 2) < 0) {
                throw new InsufficientBalanceException(
                    "Insufficient wallet balance ({$wallet->balance}) for payout amount ({$payout->amount})."
                );
            }

            $balanceBefore = $wallet->balance;
            $balanceAfter = bcsub($balanceBefore, $payout->amount, 2);

            // Release the hold at the same time - it is being converted
            // into a real debit, not undone, so it must not remain
            // reserved on top of the balance that is about to drop.
            $heldAfter = bcsub((string) $wallet->held_amount, (string) $payout->amount, 2);
            if (bccomp($heldAfter, "0", 2) < 0) {
                $heldAfter = "0.00";
            }

            $wallet->update([
                "balance" => $balanceAfter,
                "held_amount" => $heldAfter,
            ]);

            return WalletTransaction::create([
                "wallet_id" => $wallet->id,
                "merchant_id" => $payout->merchant_id,
                "type" => WalletTransaction::TYPE_DEBIT,
                "amount" => $payout->amount,
                "balance_before" => $balanceBefore,
                "balance_after" => $balanceAfter,
                "reference_type" => "payout",
                "reference_id" => $payout->id,
                "description" => "Debit for successful payout {$payout->transaction_id} (hold released)",
            ]);
        });
    }

    /**
     * Step 2b (FAILED path): gives the held amount back to the merchant's
     * available balance without ever touching the real `balance` column.
     */
    public function releaseHoldForPayout(Payout $payout): WalletTransaction
    {
        return DB::transaction(function () use ($payout) {
            $wallet = Wallet::where("merchant_id", $payout->merchant_id)->lockForUpdate()->first();

            if (! $wallet) {
                throw new \RuntimeException("Wallet not found for merchant #{$payout->merchant_id}");
            }

            $existing = WalletTransaction::where("reference_type", "payout_release")
                ->where("reference_id", $payout->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $heldBefore = $wallet->held_amount;
            $heldAfter = bcsub($heldBefore, $payout->amount, 2);
            if (bccomp($heldAfter, "0", 2) < 0) {
                $heldAfter = "0.00";
            }

            $wallet->update(["held_amount" => $heldAfter]);

            return WalletTransaction::create([
                "wallet_id" => $wallet->id,
                "merchant_id" => $payout->merchant_id,
                "type" => WalletTransaction::TYPE_RELEASE,
                "amount" => $payout->amount,
                "balance_before" => $heldBefore,
                "balance_after" => $heldAfter,
                "reference_type" => "payout_release",
                "reference_id" => $payout->id,
                "description" => "Hold released back to available balance - payout {$payout->transaction_id} failed",
            ]);
        });
    }

    /**
     * Checks against AVAILABLE balance (ledger balance minus anything
     * already on hold for other pending payouts), not the raw balance.
     */
    public function hasSufficientBalance(int $merchantId, string $amount): bool
    {
        $wallet = Wallet::where("merchant_id", $merchantId)->first();

        if (! $wallet) {
            return false;
        }

        $available = bcsub((string) $wallet->balance, (string) $wallet->held_amount, 2);

        return bccomp($available, $amount, 2) >= 0;
    }
}
