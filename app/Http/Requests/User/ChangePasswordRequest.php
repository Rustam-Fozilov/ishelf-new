<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password'
        ];
    }
}
