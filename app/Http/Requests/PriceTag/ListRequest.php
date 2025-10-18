<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'         => 'nullable|integer|min:1',
            'per_page'     => 'nullable|integer|min:1',
            'category_sku' => 'required|integer|exists:product_categories,sku',
        ];
    }
}
