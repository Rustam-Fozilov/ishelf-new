<?php

namespace App\Http\Requests\ProductShelfTemp;

use Illuminate\Foundation\Http\FormRequest;

class AutoOrderingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shelf_id'         => 'required|integer|exists:shelves,id',
            'order_priority'   => 'required|array',
            'order_priority.*' => 'required|array',
        ];
    }
}
