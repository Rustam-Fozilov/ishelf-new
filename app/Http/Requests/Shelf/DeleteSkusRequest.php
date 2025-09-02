<?php

namespace App\Http\Requests\Shelf;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSkusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_sku' => 'nullable|integer|exists:product_categories,sku',
            'branch_id'    => 'nullable|integer|exists:branches,id',
            'region_id'    => 'nullable|integer|exists:regions,id',
        ];
    }
}
