<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePhoneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => 'required|regex:/^(998)([0-9]{9})$/'
        ];
    }
}
