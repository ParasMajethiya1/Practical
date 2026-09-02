<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $wallets = Wallet::query()
            ->with("merchant")
            ->when($request->filled("search"), fn ($q) => $q->whereHas("merchant", fn ($m) => $m->where("name", "like", "%{$request->search}%")))
            ->orderByDesc("balance")
            ->paginate(15)
            ->withQueryString();

        return view("wallets.index", compact("wallets"));
    }

    public function show(Request $request, Wallet $wallet): View
    {
        $wallet->load("merchant");

        $transactions = WalletTransaction::query()
            ->where("wallet_id", $wallet->id)
            ->when($request->filled("type"), fn ($q) => $q->where("type", $request->type))
            ->when($request->filled("date_from"), fn ($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->filled("date_to"), fn ($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view("wallets.show", compact("wallet", "transactions"));
    }
}
