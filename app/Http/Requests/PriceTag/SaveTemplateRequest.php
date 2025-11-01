<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class SaveTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'   => 'nullable|integer',
            'type' => 'required|string',
            'name' => 'nullable|string',
            'data' => 'required|array',
        ];
    }
}
