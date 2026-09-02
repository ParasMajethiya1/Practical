<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayinRequest;
use App\Models\Payin;
use App\Services\PayinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayinController extends Controller
{
    public function __construct(protected PayinService $payinService)
    {
    }

    public function store(StorePayinRequest $request): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        $payin = $this->payinService->initiate($merchant, $request->validated());

        return response()->json([
            "success" => true,
            "message" => "Pay-in initiated successfully.",
            "data" => [
                "transaction_id" => $payin->transaction_id,
                "status" => $payin->status,
                "amount" => $payin->amount,
                "currency" => $payin->currency,
                "created_at" => $payin->created_at,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        $payins = $this->payinService->listFor($merchant, $request->only(["status", "date_from", "date_to", "per_page"]));

        return response()->json([
            "success" => true,
            "data" => $payins,
        ]);
    }

    public function show(Request $request, string $transaction_id): JsonResponse
    {
        $merchant = $request->attributes->get("merchant");

        $payin = Payin::where("merchant_id", $merchant->id)
            ->where("transaction_id", $transaction_id)
            ->first();

        if (! $payin) {
            return response()->json(["success" => false, "message" => "Pay-in not found."], 404);
        }

        return response()->json(["success" => true, "data" => $payin]);
    }
}
