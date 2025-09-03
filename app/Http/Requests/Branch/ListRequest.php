<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'              => 'nullable|integer|exists:branches,id',
            'search'          => 'nullable|string',
            'status'          => 'nullable|integer',
            'source'          => 'required|bool',
            'per_page'        => 'nullable|integer',
            'region_id'       => 'nullable|integer|exists:regions,id',
            'order_by'        => 'nullable|string',
            'order_direction' => 'nullable|string',
        ];
    }
}
