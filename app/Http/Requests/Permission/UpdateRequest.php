<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $is_parent = $this->get('is_parent');

        return [
            'id'        => 'required|integer|exists:permissions,id',
            'key'       => 'required|string',
            'parent_id' => 'required|integer',
            'is_parent' => 'required|integer|in:0,1',
            'title'     => 'required|string',
            'type'      => ['required', 'string', Rule::in($is_parent ? 'flag' : ['flag', 'list', 'numeric'])],
            'options'   => 'nullable|array|required_if:type,=,list'
        ];
    }
}
