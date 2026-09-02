<?php

use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix("v1")->group(function () {

    // Public - register a new merchant and receive an api_key.
    Route::post("merchants", [MerchantController::class, "store"]);

    // Everything below requires the X-API-KEY header (see AuthenticateMerchant middleware).
    Route::middleware("auth.merchant")->group(function () {
        Route::post("payins", [PayinController::class, "store"]);
        Route::get("payins", [PayinController::class, "index"]);
        Route::get("payins/{transaction_id}", [PayinController::class, "show"]);

        Route::post("payouts", [PayoutController::class, "store"]);
        Route::get("payouts", [PayoutController::class, "index"]);
        Route::get("payouts/{transaction_id}", [PayoutController::class, "show"]);

        Route::get("wallet", [WalletController::class, "show"]);
    });
});
