<?php

namespace App\Http\Requests\ProductParameter;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page'        => 'nullable|integer|min:1',
            'page'            => 'nullable|integer|min:1',
            'search'          => 'nullable|string',
            'category_sku'    => 'nullable|exists:product_categories,sku',
            'order_by'        => 'nullable|string',
            'order_direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
