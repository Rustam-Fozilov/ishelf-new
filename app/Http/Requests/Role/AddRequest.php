<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class AddRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'                  => 'required|string',
            'color'                  => 'required|string',
            'users'                  => 'nullable|array|min:1',
            'users.*'                => 'nullable|integer|exists:users,id|distinct',
            'allows'                 => 'nullable|array|min:1',
            'allows.*.value'         => 'nullable|string|in:0,1,all,own',
            'allows.*.permission_id' => 'nullable|integer|exists:permissions,id|distinct',
            'category_must_be_added' => 'required|boolean',
        ];
    }
}
