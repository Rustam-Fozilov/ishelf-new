<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class AddRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_sku' => 'required|integer|exists:product_categories,sku',
            'region_id'    => 'nullable|required_without:branch_id|array',
            'region_id.*'  => 'nullable|integer|exists:regions,id',
            'branch_id'    => 'nullable|required_without:region_id|array',
            'branch_id.*'  => 'nullable|integer|exists:branches,id',
            'upload_id'    => 'nullable|integer|exists:uploads,id',
            'document_id'  => 'nullable|integer|exists:uploads,id',
            'comment'      => 'nullable|string',
        ];
    }
}
