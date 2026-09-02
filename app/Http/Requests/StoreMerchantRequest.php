<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:150"],
            "email" => ["required", "email", "max:150", "unique:merchants,email"],
            "phone" => ["nullable", "string", "max:20"],
            "status" => ["sometimes", "in:active,inactive"],
        ];
    }
}
