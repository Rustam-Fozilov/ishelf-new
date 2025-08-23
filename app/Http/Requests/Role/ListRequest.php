<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'with_user' => 'nullable|boolean',
        ];
    }
}
