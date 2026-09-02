<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMerchantRequest;
use App\Http\Requests\UpdateMerchantRequest;
use App\Models\Merchant;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MerchantController extends Controller
{
    public function index(Request $request): View
    {
        $merchants = Merchant::query()
            ->withCount(["payins", "payouts"])
            ->with("wallet")
            ->when($request->filled("search"), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where("name", "like", "%{$request->search}%")
                    ->orWhere("email", "like", "%{$request->search}%");
            }))
            ->when($request->filled("status"), fn ($q) => $q->where("status", $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view("merchants.index", compact("merchants"));
    }

    public function create(): View
    {
        return view("merchants.create");
    }

    public function store(StoreMerchantRequest $request): RedirectResponse
    {
        $merchant = DB::transaction(function () use ($request) {
            $merchant = Merchant::create([
                ...$request->validated(),
                "api_key" => Str::random(40),
                "status" => $request->validated("status", "active"),
            ]);

            Wallet::create([
                "merchant_id" => $merchant->id,
                "balance" => 0,
                "currency" => "INR",
            ]);

            return $merchant;
        });

        return redirect()->route("merchants.show", $merchant)
            ->with("status", "Merchant created successfully. API key: {$merchant->api_key}");
    }

    public function show(Merchant $merchant): View
    {
        $merchant->load("wallet");
        $recentPayins = $merchant->payins()->latest()->limit(10)->get();
        $recentPayouts = $merchant->payouts()->latest()->limit(10)->get();

        return view("merchants.show", compact("merchant", "recentPayins", "recentPayouts"));
    }

    public function edit(Merchant $merchant): View
    {
        return view("merchants.edit", compact("merchant"));
    }

    public function update(UpdateMerchantRequest $request, Merchant $merchant): RedirectResponse
    {
        $merchant->update($request->validated());

        return redirect()->route("merchants.show", $merchant)->with("status", "Merchant updated successfully.");
    }

    public function destroy(Merchant $merchant): RedirectResponse
    {
        if ($merchant->wallet && bccomp($merchant->wallet->balance, "0", 2) > 0) {
            return back()->with("error", "Cannot delete a merchant with a non-zero wallet balance.");
        }

        $merchant->delete();

        return redirect()->route("merchants.index")->with("status", "Merchant deleted successfully.");
    }
}
