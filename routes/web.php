<?php

use App\Http\Controllers\MerchantController;
use App\Http\Controllers\PayinController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin / back-office web routes
|--------------------------------------------------------------------------
| Simple Blade-based CRUD/listing pages for internal staff use.
| Pay-ins and payouts are created via the API only (see routes/api.php);
| here we only list/view them plus manage merchants and wallets.
*/

Route::redirect("/", "/merchants");

Route::resource("merchants", MerchantController::class);

Route::get("payins", [PayinController::class, "index"])->name("payins.index");
Route::get("payins/{payin}", [PayinController::class, "show"])->name("payins.show");

Route::get("payouts", [PayoutController::class, "index"])->name("payouts.index");
Route::get("payouts/{payout}", [PayoutController::class, "show"])->name("payouts.show");

Route::get("wallets", [WalletController::class, "index"])->name("wallets.index");
Route::get("wallets/{wallet}", [WalletController::class, "show"])->name("wallets.show");
