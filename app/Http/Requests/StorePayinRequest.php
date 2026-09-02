<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayinRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Merchant identity/authorization is handled by the
        // AuthenticateMerchant middleware before this request is validated.
        return true;
    }

    public function rules(): array
    {
        return [
            "amount" => ["required", "numeric", "min:0.01", "max:99999999.99"],
            "currency" => ["sometimes", "string", "size:3"],
            "payment_method" => ["nullable", "string", "max:50"],
            "customer_name" => ["required", "string", "max:150"],
            "customer_email" => ["nullable", "email", "max:150"],
            "customer_phone" => ["nullable", "string", "max:20"],
            "remarks" => ["nullable", "string", "max:255"],
            "meta" => ["nullable", "array"],
        ];
    }
}
