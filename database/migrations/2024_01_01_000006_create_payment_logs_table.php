<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("payment_logs", function (Blueprint $table) {
            $table->id();
            $table->string("transaction_id", 40)->nullable()->index();
            $table->string("loggable_type")->nullable();
            $table->unsignedBigInteger("loggable_id")->nullable();
            $table->string("event");
            $table->string("status")->nullable();
            $table->text("message")->nullable();
            $table->json("context")->nullable();
            $table->timestamps();

            $table->index(["loggable_type", "loggable_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payment_logs");
    }
};
