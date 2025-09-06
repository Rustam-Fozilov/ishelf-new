<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class MakeFamilyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'skus'         => 'required|array',
            'skus.*.sku'   => 'required|exists:products,sku',
            'skus.*.order' => 'required|integer',
        ];
    }
}
