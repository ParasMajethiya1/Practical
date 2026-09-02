<?php

use App\Http\Controllers\Auth\AdminAuthController;
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
| Pay-ins are created via the API only (see routes/api.php); payouts can
| also be initiated here for demo/testing purposes (PayoutController@store),
| which places the amount on hold via PayoutService::initiate().
|
| Everything below is gated behind the "admin" guard (session-based,
| see config/auth.php + app/Models/Admin.php). Unauthenticated visitors
| are redirected to /login. This is separate from merchant API auth
| (X-API-KEY, see AuthenticateMerchant) - staff credentials never grant
| API access and vice versa.
*/

Route::get("login", [AdminAuthController::class, "showLoginForm"])->name("login")->middleware("guest:admin");
Route::post("login", [AdminAuthController::class, "login"])->middleware("guest:admin");
Route::post("logout", [AdminAuthController::class, "logout"])->name("logout")->middleware("auth:admin");

Route::middleware("auth:admin")->group(function () {
    Route::redirect("/", "/merchants");

    Route::resource("merchants", MerchantController::class);

    Route::get("payins", [PayinController::class, "index"])->name("payins.index");
    Route::get("payins/{payin}", [PayinController::class, "show"])->name("payins.show");

    Route::get("payouts", [PayoutController::class, "index"])->name("payouts.index");
    Route::get("payouts/create", [PayoutController::class, "create"])->name("payouts.create");
    Route::post("payouts", [PayoutController::class, "store"])->name("payouts.store");
    Route::post("payouts/process-pending", [PayoutController::class, "processPending"])->name("payouts.process-pending");
    Route::get("payouts/{payout}", [PayoutController::class, "show"])->name("payouts.show");

    Route::get("wallets", [WalletController::class, "index"])->name("wallets.index");
    Route::get("wallets/{wallet}", [WalletController::class, "show"])->name("wallets.show");
});
