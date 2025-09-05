<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'         => 'nullable|integer',
            'per_page'     => 'nullable|integer',
            'status'       => 'nullable|integer|in:1,2',
            'search'       => 'nullable|string',
            'category_sku' => 'nullable|integer|exists:product_categories,sku',
        ];
    }
}
