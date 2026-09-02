<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchantRequest;
use App\Models\Merchant;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MerchantController extends Controller
{
    /**
     * Public merchant registration endpoint - returns the generated
     * api_key ONCE. Store it securely; it cannot be retrieved again
     * through the API.
     */
    public function store(StoreMerchantRequest $request): JsonResponse
    {
        $merchant = DB::transaction(function () use ($request) {
            $merchant = Merchant::create([
                "name" => $request->validated("name"),
                "email" => $request->validated("email"),
                "phone" => $request->validated("phone"),
                "api_key" => Str::random(40),
                "status" => "active",
            ]);

            Wallet::create([
                "merchant_id" => $merchant->id,
                "balance" => 0,
                "currency" => "INR",
            ]);

            return $merchant;
        });

        return response()->json([
            "success" => true,
            "message" => "Merchant registered successfully. Store the api_key securely - it will not be shown again.",
            "data" => [
                "id" => $merchant->id,
                "name" => $merchant->name,
                "email" => $merchant->email,
                "api_key" => $merchant->api_key,
            ],
        ], 201);
    }
}
