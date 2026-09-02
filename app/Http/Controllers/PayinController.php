<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Payin;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayinController extends Controller
{
    public function index(Request $request): View
    {
        $payins = Payin::query()
            ->with("merchant")
            ->status($request->status)
            ->forMerchant($request->merchant_id)
            ->betweenDates($request->date_from, $request->date_to)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $merchants = Merchant::orderBy("name")->get(["id", "name"]);

        return view("payins.index", compact("payins", "merchants"));
    }

    public function show(Payin $payin): View
    {
        $payin->load("merchant");

        return view("payins.show", compact("payin"));
    }
}
