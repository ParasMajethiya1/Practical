<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
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

    public function show(Payout $payout): View
    {
        $payout->load("merchant");

        return view("payouts.show", compact("payout"));
    }
}
