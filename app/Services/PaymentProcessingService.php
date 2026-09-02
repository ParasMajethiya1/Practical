<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Payin;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

/**
 * Runs on the scheduler (see app/Console/Commands/ProcessPendingPayments.php).
 *
 * For every PENDING payin/payout it randomly resolves the status to one of
 * SUCCESS / FAILED / PENDING. Anything left PENDING is picked up again on
 * the next run automatically (we simply never stop querying for PENDING
 * rows).
 *
 * Duplicate-processing protection:
 *  - Each row is re-fetched with `lockForUpdate()` INSIDE its own DB
 *    transaction, and its status is re-checked before mutating it. If two
 *    scheduler runs somehow overlap, the second one will see the row is no
 *    longer PENDING (or will block until the first transaction commits,
 *    then see the updated status) and skip it.
 *  - Wallet balance updates additionally rely on the unique
 *    (reference_type, reference_id) constraint in WalletService, so even a
 *    programming mistake that calls the credit/debit logic twice cannot
 *    move the balance twice for the same transaction.
 */
class PaymentProcessingService
{
    protected const POSSIBLE_STATUSES = ["SUCCESS", "FAILED", "PENDING"];

    public function __construct(protected WalletService $walletService)
    {
    }

    public function run(int $batchSize = 100): array
    {
        $summary = [
            "payins" => ["checked" => 0, "success" => 0, "failed" => 0, "still_pending" => 0],
            "payouts" => ["checked" => 0, "success" => 0, "failed" => 0, "still_pending" => 0],
        ];

        Payin::query()
            ->where("status", Payin::STATUS_PENDING)
            ->orderBy("id")
            ->chunkById($batchSize, function ($payins) use (&$summary) {
                foreach ($payins as $payin) {
                    $result = $this->processPayin($payin);
                    $summary["payins"]["checked"]++;
                    $summary["payins"][$result]++;
                }
            });

        Payout::query()
            ->where("status", Payout::STATUS_PENDING)
            ->orderBy("id")
            ->chunkById($batchSize, function ($payouts) use (&$summary) {
                foreach ($payouts as $payout) {
                    $result = $this->processPayout($payout);
                    $summary["payouts"]["checked"]++;
                    $summary["payouts"][$result]++;
                }
            });

        return $summary;
    }

    /**
     * @return string one of: success, failed, still_pending
     */
    protected function processPayin(Payin $payin): string
    {
        return DB::transaction(function () use ($payin) {
            /** @var Payin $locked */
            $locked = Payin::where("id", $payin->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->isPending()) {
                // Already handled by another run/worker in the meantime.
                return "still_pending";
            }

            $status = $this->randomStatus();

            if ($status === Payin::STATUS_PENDING) {
                PaymentLogger::log($locked, "CRON_CHECK", Payin::STATUS_PENDING, "Pay-in still pending, will retry next run.");
                return "still_pending";
            }

            $locked->status = $status;
            $locked->processed_at = now();

            if ($status === Payin::STATUS_SUCCESS) {
                $locked->save();

                $this->walletService->creditForPayin($locked);

                PaymentLogger::log($locked, "PROCESSED", Payin::STATUS_SUCCESS, "Pay-in processed successfully, wallet credited.");

                return "success";
            }

            // FAILED
            $locked->failure_reason = "Randomly resolved to FAILED by the payment processing simulator.";
            $locked->save();

            PaymentLogger::log($locked, "PROCESSED", Payin::STATUS_FAILED, "Pay-in processing failed.");

            return "failed";
        });
    }

    protected function processPayout(Payout $payout): string
    {
        return DB::transaction(function () use ($payout) {
            /** @var Payout $locked */
            $locked = Payout::where("id", $payout->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->isPending()) {
                return "still_pending";
            }

            $status = $this->randomStatus();

            if ($status === Payout::STATUS_PENDING) {
                PaymentLogger::log($locked, "CRON_CHECK", Payout::STATUS_PENDING, "Payout still pending, will retry next run.");
                return "still_pending";
            }

            if ($status === Payout::STATUS_SUCCESS) {
                try {
                    $locked->status = Payout::STATUS_SUCCESS;
                    $locked->processed_at = now();
                    $locked->save();

                    // Converts the existing HOLD into a real debit - the
                    // held funds are released and the ledger balance drops
                    // in the same wallet-locked operation.
                    $this->walletService->debitForPayout($locked);

                    PaymentLogger::log($locked, "PROCESSED", Payout::STATUS_SUCCESS, "Payout processed successfully, hold released and wallet debited.");

                    return "success";
                } catch (InsufficientBalanceException $e) {
                    // Balance dropped below the required amount between
                    // initiation and processing - force FAILED instead and
                    // give the hold back to the merchant.
                    $locked->status = Payout::STATUS_FAILED;
                    $locked->failure_reason = $e->getMessage();
                    $locked->save();

                    $this->walletService->releaseHoldForPayout($locked);

                    PaymentLogger::error($locked, "PROCESSING_FAILED", $e->getMessage());

                    return "failed";
                }
            }

            // FAILED - give the held amount back to the merchant's
            // available balance. The real ledger `balance` is never
            // touched for a failed payout.
            $locked->status = Payout::STATUS_FAILED;
            $locked->processed_at = now();
            $locked->failure_reason = "Randomly resolved to FAILED by the payment processing simulator.";
            $locked->save();

            $this->walletService->releaseHoldForPayout($locked);

            PaymentLogger::log($locked, "PROCESSED", Payout::STATUS_FAILED, "Payout processing failed, hold released back to available balance.");

            return "failed";
        });
    }

    protected function randomStatus(): string
    {
        return self::POSSIBLE_STATUSES[array_rand(self::POSSIBLE_STATUSES)];
    }
}
