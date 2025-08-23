<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search'          => 'nullable|min:2|string',
            'per_page'        => 'nullable|integer|min:1',
            'role_id'         => 'nullable|integer|exists:roles,id',
            'region_id'       => 'nullable|integer|exists:regions,id',
            'status'          => 'nullable|boolean',
            'order_by'        => 'nullable|string',
            'order_direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
