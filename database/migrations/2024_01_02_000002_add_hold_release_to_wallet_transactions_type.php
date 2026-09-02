<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widens the wallet_transactions.type enum to add HOLD and RELEASE,
     * used by the payout hold/release flow in WalletService:
     *   HOLD    - amount reserved when a payout is initiated (funds stay
     *             in `balance` but become unavailable via `held_amount`).
     *   RELEASE - the hold is given back to available funds because the
     *             payout ended in FAILED.
     *   DEBIT   - (existing) the hold is converted into a real debit
     *             because the payout ended in SUCCESS.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === "mysql") {
            DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('CREDIT','DEBIT','HOLD','RELEASE') NOT NULL");
            return;
        }

        if ($driver === "pgsql") {
            DB::statement("ALTER TABLE wallet_transactions ALTER COLUMN type TYPE VARCHAR(20)");
            return;
        }

        // sqlite (and others) don't enforce enum check constraints the same
        // way, and Laravel's schema builder has no portable "modify enum"
        // helper, so there is nothing further to do there.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === "mysql") {
            DB::statement("ALTER TABLE wallet_transactions MODIFY type ENUM('CREDIT','DEBIT') NOT NULL");
        }
    }
};
