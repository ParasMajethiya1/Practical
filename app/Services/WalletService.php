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
 *     to credit/debit the wallet twice for the same Payin/Payout, even
 *     if this method is accidentally called twice or two cron workers
 *     race each other.
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

            $wallet->update(["balance" => $balanceAfter]);

            return WalletTransaction::create([
                "wallet_id" => $wallet->id,
                "merchant_id" => $payout->merchant_id,
                "type" => WalletTransaction::TYPE_DEBIT,
                "amount" => $payout->amount,
                "balance_before" => $balanceBefore,
                "balance_after" => $balanceAfter,
                "reference_type" => "payout",
                "reference_id" => $payout->id,
                "description" => "Debit for successful payout {$payout->transaction_id}",
            ]);
        });
    }

    public function hasSufficientBalance(int $merchantId, string $amount): bool
    {
        $wallet = Wallet::where("merchant_id", $merchantId)->first();

        return $wallet && bccomp($wallet->balance, $amount, 2) >= 0;
    }
}
