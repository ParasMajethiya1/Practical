<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        "transaction_id",
        "loggable_type",
        "loggable_id",
        "event",
        "status",
        "message",
        "context",
    ];

    protected $casts = [
        "context" => "array",
    ];

    public function loggable()
    {
        return $this->morphTo();
    }
}
