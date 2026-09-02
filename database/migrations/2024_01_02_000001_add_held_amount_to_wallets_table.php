<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a `held_amount` column to wallets.
     *
     * `balance`        = the merchant's total ledger balance.
     * `held_amount`    = the portion of `balance` currently reserved
     *                    against PENDING payouts (see WalletService).
     * available funds  = balance - held_amount (Wallet::getAvailableBalanceAttribute()).
     */
    public function up(): void
    {
        Schema::table("wallets", function (Blueprint $table) {
            $table->decimal("held_amount", 15, 2)->default(0)->after("balance");
        });
    }

    public function down(): void
    {
        Schema::table("wallets", function (Blueprint $table) {
            $table->dropColumn("held_amount");
        });
    }
};
