<?php

namespace App\Console\Commands;

use App\Services\PaymentProcessingService;
use Illuminate\Console\Command;

class ProcessPendingPayments extends Command
{
    /**
     * php artisan payments:process-pending
     */
    protected $signature = "payments:process-pending {--batch=100 : Number of rows to process per chunk}";

    protected $description = "Process all PENDING pay-ins and payouts, randomly resolving each to SUCCESS, FAILED, or PENDING.";

    public function handle(PaymentProcessingService $service): int
    {
        $this->info("Processing pending payments...");

        $summary = $service->run((int) $this->option("batch"));

        $this->table(
            ["Type", "Checked", "Success", "Failed", "Still Pending"],
            [
                ["Pay-ins", $summary["payins"]["checked"], $summary["payins"]["success"], $summary["payins"]["failed"], $summary["payins"]["still_pending"]],
                ["Payouts", $summary["payouts"]["checked"], $summary["payouts"]["success"], $summary["payouts"]["failed"], $summary["payouts"]["still_pending"]],
            ]
        );

        $this->info("Done.");

        return self::SUCCESS;
    }
}
