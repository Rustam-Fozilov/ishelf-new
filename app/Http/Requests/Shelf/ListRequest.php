<?php

namespace App\Http\Requests\Shelf;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_id'       => 'nullable|integer|exists:branches,id',
            'category_sku'    => 'nullable|integer|exists:product_categories,sku',
            'type'            => 'nullable|integer',
            'floor'           => 'nullable|integer',
            'is_paddon'       => 'nullable|integer|in:0,1',
            'region_id'       => 'nullable|integer|exists:regions,id',
            'page'            => 'nullable|integer|min:1',
            'per_page'        => 'nullable|integer|min:1',
            'order_by'        => 'nullable|string',
            'order_direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
