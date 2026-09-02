<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $merchant = $this->route("merchant");

        return [
            "name" => ["required", "string", "max:150"],
            "email" => ["required", "email", "max:150", Rule::unique("merchants", "email")->ignore($merchant?->id)],
            "phone" => ["nullable", "string", "max:20"],
            "status" => ["sometimes", "in:active,inactive"],
        ];
    }
}
