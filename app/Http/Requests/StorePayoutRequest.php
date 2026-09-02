<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Only used by the web admin form (routes/web.php); the API
            // resolves the merchant from the api_key middleware instead
            // and ignores this field.
            "merchant_id" => ["sometimes", "nullable", "exists:merchants,id"],
            "amount" => ["required", "numeric", "min:0.01", "max:99999999.99"],
            "currency" => ["sometimes", "string", "size:3"],
            "payout_method" => ["nullable", "string", "max:50"],
            "beneficiary_name" => ["required", "string", "max:150"],
            "beneficiary_account_number" => ["required", "string", "max:34"],
            "beneficiary_ifsc" => ["nullable", "string", "max:20"],
            "beneficiary_bank_name" => ["nullable", "string", "max:150"],
            "remarks" => ["nullable", "string", "max:255"],
            "meta" => ["nullable", "array"],
        ];
    }
}
