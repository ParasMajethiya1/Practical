<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ProcessPendingPayments::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Runs every minute, checks every PENDING payin/payout, and
        // resolves each one to SUCCESS / FAILED / still PENDING.
        // withoutOverlapping() ensures a slow run never overlaps the next
        // tick, and the row-level locking inside PaymentProcessingService
        // guards against double-processing even if overlap protection
        // were ever bypassed (e.g. multiple servers running the scheduler).
        $schedule->command("payments:process-pending")
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . "/Commands");

        require base_path("routes/console.php");
    }
}
