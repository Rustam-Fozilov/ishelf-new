<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search'          => 'nullable|string',
            'category_sku'    => 'nullable|integer|exists:product_categories,sku',
            'step'            => 'nullable|integer',
            'status'          => 'nullable|integer',
            'branch_id'       => 'nullable|array',
            'branch_id.*'     => 'nullable|integer|exists:branches,id',
            'region_id'       => 'nullable|array',
            'region_id.*'     => 'nullable|integer|exists:regions,id',
            'order_by'        => 'nullable|string',
            'order_direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
