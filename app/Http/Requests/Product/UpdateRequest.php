<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'size'     => 'nullable|integer',
            'height'   => 'nullable|integer',
            'dioganal' => 'nullable|integer',
            'weight'   => 'nullable|numeric',
        ];
    }
}
