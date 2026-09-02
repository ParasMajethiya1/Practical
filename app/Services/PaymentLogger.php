<?php

namespace App\Services;

use App\Models\PaymentLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Central place for recording payment-related events.
 * Every important event is written BOTH to the database (payment_logs
 * table, so it is filterable/visible from the admin UI) and to the
 * standard Laravel log (storage/logs/laravel.log), so it also shows
 * up in log aggregation tools / tail -f during development.
 */
class PaymentLogger
{
    public static function log(?Model $model, string $event, ?string $status, string $message, array $context = []): void
    {
        $transactionId = $model->transaction_id ?? null;

        PaymentLog::create([
            "transaction_id" => $transactionId,
            "loggable_type" => $model ? get_class($model) : null,
            "loggable_id" => $model->id ?? null,
            "event" => $event,
            "status" => $status,
            "message" => $message,
            "context" => $context,
        ]);

        Log::info("[PAYMENT] {$event}" . ($transactionId ? " ({$transactionId})" : ""), array_merge([
            "status" => $status,
            "message" => $message,
        ], $context));
    }

    public static function error(?Model $model, string $event, string $message, array $context = []): void
    {
        $transactionId = $model->transaction_id ?? null;

        PaymentLog::create([
            "transaction_id" => $transactionId,
            "loggable_type" => $model ? get_class($model) : null,
            "loggable_id" => $model->id ?? null,
            "event" => $event,
            "status" => "ERROR",
            "message" => $message,
            "context" => $context,
        ]);

        Log::error("[PAYMENT ERROR] {$event}" . ($transactionId ? " ({$transactionId})" : ""), array_merge([
            "message" => $message,
        ], $context));
    }
}
