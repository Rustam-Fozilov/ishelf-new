<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttributeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'upload_id'    => 'required|integer|exists:uploads,id',
            'category_sku' => 'required|integer|exists:product_categories,sku',
        ];
    }
}
