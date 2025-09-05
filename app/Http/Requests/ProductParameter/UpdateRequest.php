<?php

namespace App\Http\Requests\ProductParameter;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_sku'            => 'required|exists:product_categories,sku',
            'parameters'              => 'required|array',
            'parameters.*.key'        => 'required|string|exists:parameters,key',
            'parameters.*.name'       => 'nullable|string',
            'parameters.*.ordering'   => 'nullable|integer',
            'parameters.*.icon_id'    => 'nullable|integer|exists:uploads,id',
            'parameters.*.short_name' => 'nullable|string',
        ];
    }
}
