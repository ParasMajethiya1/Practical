<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayoutRequest;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(protected PayoutService $payoutService)
    {
    }

    public function store(StorePayoutRequest $request): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        try {
            $payout = $this->payoutService->initiate($merchant, $request->validated());
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            "success" => true,
            "message" => "Payout initiated successfully.",
            "data" => [
                "transaction_id" => $payout->transaction_id,
                "status" => $payout->status,
                "amount" => $payout->amount,
                "currency" => $payout->currency,
                "created_at" => $payout->created_at,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        $payouts = $this->payoutService->listFor($merchant, $request->only(["status", "date_from", "date_to", "per_page"]));

        return response()->json([
            "success" => true,
            "data" => $payouts,
        ]);
    }

    public function show(Request $request, string $transaction_id): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        $payout = Payout::where("merchant_id", $merchant->id)
            ->where("transaction_id", $transaction_id)
            ->first();

        if (! $payout) {
            return response()->json(["success" => false, "message" => "Payout not found."], 404);
        }

        return response()->json(["success" => true, "data" => $payout]);
    }
}
