<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStepRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'step'    => 'required|integer|in:1,2,3',
            'app_id'  => 'required|integer|exists:applications,id',
            'comment' => 'nullable|string',
        ];
    }
}
