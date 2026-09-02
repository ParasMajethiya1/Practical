<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("wallet_transactions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("wallet_id")->constrained("wallets")->cascadeOnDelete();
            $table->foreignId("merchant_id")->constrained("merchants")->cascadeOnDelete();
            $table->enum("type", ["CREDIT", "DEBIT"]);
            $table->decimal("amount", 15, 2);
            $table->decimal("balance_before", 15, 2);
            $table->decimal("balance_after", 15, 2);
            $table->string("reference_type");
            $table->unsignedBigInteger("reference_id");
            $table->string("description")->nullable();
            $table->timestamps();

            $table->unique(["reference_type", "reference_id"], "wallet_tx_reference_unique");
            $table->index(["wallet_id", "created_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("wallet_transactions");
    }
};
