<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");
        $wallet = $merchant->wallet;

        return response()->json([
            "success" => true,
            "data" => [
                "merchant" => $merchant->name,
                "balance" => $wallet->balance,
                "currency" => $wallet->currency,
                "recent_transactions" => $wallet->transactions()->latest()->limit(20)->get(),
            ],
        ]);
    }
}
