<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class AddPrintTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'types'              => 'required|array',
            'types.*.print_type' => 'required|string',
            'types.*.categories' => 'nullable|exists:product_categories,sku'
        ];
    }
}
