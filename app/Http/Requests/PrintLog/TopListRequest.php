<?php

namespace App\Http\Requests\PrintLog;

use Illuminate\Foundation\Http\FormRequest;

class TopListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_id'       => 'nullable|integer|exists:branches,id',
            'region_id'       => 'nullable|integer|exists:regions,id',
            'category_sku'    => 'nullable|integer|exists:product_categories,sku',
            'type'            => 'nullable|integer',
            'floor'           => 'nullable|integer',
            'is_paddon'       => 'nullable|integer',
            'order_by'        => 'nullable|string|in:id,created_at,branch_id,type,product_sold_count,product_shelf_count',
            'order_direction' => 'nullable|string|in:asc,desc',
            'page'            => 'nullable|integer',
            'per_page'        => 'nullable|integer|min:1',
        ];
    }
}
