<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'       => 'nullable|integer|exists:product_categories,id',
            'sku'      => 'nullable|integer|exists:product_categories,sku',
            'search'   => 'nullable|string',
            'page'     => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1'
        ];
    }
}
