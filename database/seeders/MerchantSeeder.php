<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MerchantSeeder extends Seeder
{
    /**
     * Creates a couple of demo merchants (with wallets) and prints their
     * api_key to the console so you can immediately try the API.
     */
    public function run(): void
    {
        $demoMerchants = [
            ["name" => "Acme Retail Pvt Ltd", "email" => "acme@example.com", "opening_balance" => 50000],
            ["name" => "Bright Traders", "email" => "bright@example.com", "opening_balance" => 10000],
        ];

        foreach ($demoMerchants as $data) {
            $merchant = Merchant::firstOrCreate(
                ["email" => $data["email"]],
                [
                    "name" => $data["name"],
                    "api_key" => Str::random(40),
                    "status" => "active",
                ]
            );

            $wallet = Wallet::firstOrCreate(
                ["merchant_id" => $merchant->id],
                ["balance" => $data["opening_balance"], "currency" => "INR"]
            );

            $this->command->info("Merchant: {$merchant->name} | api_key: {$merchant->api_key} | wallet balance: {$wallet->balance}");
        }
    }
}
