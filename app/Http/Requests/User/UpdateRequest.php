<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'         => 'required|string',
            'surname'      => 'required|string',
            'patronymic'   => 'nullable|string',
            'phone'        => 'required|regex:/^(998)([0-9]{9})$/',
            'pinfl'        => 'required|regex:/^[0-9]{14}$/',
            'password'     => 'nullable|string|min:6',
            'role_id'      => 'required|integer|exists:roles,id',
            'branches'     => 'required|array',
            'branches.*'   => 'required|integer|distinct|exists:branches,id',
            'categories'   => 'nullable|array',
            'categories.*' => 'nullable|integer|distinct|exists:categories,id',
        ];
    }
}
