<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class AddTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type'         => 'required|int|in:1,2,3',
            'category_sku' => 'required|integer|exists:product_categories,sku',
        ];
    }
}
