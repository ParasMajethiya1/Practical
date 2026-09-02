<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\StorePayoutRequest;
use App\Models\Merchant;
use App\Models\Payout;
use App\Models\WalletTransaction;
use App\Services\PaymentProcessingService;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(protected PayoutService $payoutService)
    {
    }

    public function index(Request $request): View
    {
        $payouts = Payout::query()
            ->with("merchant")
            ->status($request->status)
            ->forMerchant($request->merchant_id)
            ->betweenDates($request->date_from, $request->date_to)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $merchants = Merchant::orderBy("name")->get(["id", "name"]);

        return view("payouts.index", compact("payouts", "merchants"));
    }

    public function create(): View
    {
        $merchants = Merchant::with("wallet")->orderBy("name")->get();

        return view("payouts.create", compact("merchants"));
    }

    /**
     * Creates a payout from the admin form. Under the hood this goes
     * through PayoutService::initiate(), which immediately places the
     * payout amount ON HOLD in the merchant's wallet - see
     * app/Services/WalletService.php for the full hold/release lifecycle.
     */
    public function store(StorePayoutRequest $request): RedirectResponse
    {
        $merchantId = $request->validated("merchant_id");

        if (! $merchantId) {
            return back()->withInput()->withErrors(["merchant_id" => "Please select a merchant."]);
        }

        $merchant = Merchant::findOrFail($merchantId);

        try {
            $payout = $this->payoutService->initiate($merchant, $request->validated());
        } catch (InsufficientBalanceException $e) {
            return back()->withInput()->with("error", $e->getMessage());
        }

        return redirect()
            ->route("payouts.show", $payout)
            ->with("status", "Payout {$payout->transaction_id} initiated - ".number_format($payout->amount, 2)." {$payout->currency} has been placed ON HOLD for {$merchant->name} and will be released automatically once processed.");
    }

    public function show(Payout $payout): View
    {
        $payout->load("merchant");

        $holdHistory = WalletTransaction::query()
            ->whereIn("reference_type", ["payout_hold", "payout", "payout_release"])
            ->where("reference_id", $payout->id)
            ->orderBy("created_at")
            ->get();

        return view("payouts.show", compact("payout", "holdHistory"));
    }

    /**
     * Manually runs the payment processing simulator so pending payouts
     * (and pay-ins) get resolved to SUCCESS/FAILED right away instead of
     * waiting for the scheduler - handy for demoing the hold -> release
     * (or hold -> debit) flow from the admin UI.
     */
    public function processPending(PaymentProcessingService $service): RedirectResponse
    {
        $summary = $service->run();

        $message = "Processed {$summary['payouts']['checked']} payout(s): "
            ."{$summary['payouts']['success']} succeeded (hold converted to debit), "
            ."{$summary['payouts']['failed']} failed (hold released), "
            ."{$summary['payouts']['still_pending']} still pending. "
            ."Pay-ins: {$summary['payins']['success']} succeeded, {$summary['payins']['failed']} failed.";

        return back()->with("status", $message);
    }
}
