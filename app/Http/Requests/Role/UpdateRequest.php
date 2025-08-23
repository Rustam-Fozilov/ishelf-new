<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'                     => 'required|integer|exists:roles,id',
            'title'                  => 'required|string',
            'color'                  => 'nullable|string',
            'allows'                 => 'nullable|array|min:1',
            'users'                  => 'nullable|array|min:1',
            'users.*'                => 'nullable|integer|exists:users,id',
            'allows.*.value'         => 'nullable|string|in:1,0,all,own',
            'allows.*.permission_id' => 'nullable|integer|exists:permissions,id',
            'category_must_be_added' => 'required|boolean',
        ];
    }
}
