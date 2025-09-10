<?php

namespace App\Http\Requests\AutoOrdering;

use Illuminate\Foundation\Http\FormRequest;

class SaveAutoOrderingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shelf_id'                         => 'required|integer|exists:shelves,id',
            'order_priority'                   => 'required|array',
            'order_priority.*.order_by'        => 'nullable|string',
            'order_priority.*.order_direction' => 'nullable|string',
        ];
    }
}
