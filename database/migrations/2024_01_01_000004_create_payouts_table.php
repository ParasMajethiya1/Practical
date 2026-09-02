<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("payouts", function (Blueprint $table) {
            $table->id();
            $table->string("transaction_id", 40)->unique();
            $table->foreignId("merchant_id")->constrained("merchants")->cascadeOnDelete();
            $table->decimal("amount", 15, 2);
            $table->string("currency", 3)->default("INR");
            $table->enum("status", ["PENDING", "SUCCESS", "FAILED"])->default("PENDING");
            $table->string("payout_method")->nullable();
            $table->json("beneficiary_details")->nullable();
            $table->text("remarks")->nullable();
            $table->text("failure_reason")->nullable();
            $table->json("meta")->nullable();
            $table->timestamp("processed_at")->nullable();
            $table->timestamps();

            $table->index(["merchant_id", "status"]);
            $table->index(["status", "created_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payouts");
    }
};
