<?php

namespace App\Http\Requests\RolePerms;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'                     => 'required|integer',
            'update_with'            => 'required|string|in:role,permission',
            'allows'                 => 'required|array',
            'allows.*.role_id'       => 'required_if:update_with,role|nullable|integer',
            'allows.*.permission_id' => 'required_if:update_with,permission|nullable|integer',
            'allows.*.value'         => 'required',
        ];
    }
}
