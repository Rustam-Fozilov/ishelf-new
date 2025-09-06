<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ToggleStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku'    => 'required|exists:products,sku',
            'status' => 'required|boolean',
        ];
    }
}
