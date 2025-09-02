<?php

namespace App\Http\Requests\ShelfStockPriority;

use Illuminate\Foundation\Http\FormRequest;

class AddShelfStockPriorityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_sku' => 'required_if:skus,=,null|integer|in:934',
            'skus'         => 'required_if:category_sku,=,null|array',
            'skus.*.order' => 'required_if:category_sku,=,null|integer',
            'skus.*.sku'   => 'required_if:category_sku,=,null|exists:products,sku',

            'items'             => 'required_if:category_sku,=,934|array',
            'items.*.floor'     => 'required_if:category_sku,=,934|integer',
            'items.*.brand_sku' => 'required_if:category_sku,=,934|integer',
        ];
    }
}
