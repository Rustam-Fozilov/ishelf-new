<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_id' => 'required|integer|exists:branches,id',
            'status'    => 'required|integer|in:0,1'
        ];
    }
}
