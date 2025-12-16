<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CheckSmsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'otp'   => 'required|string|size:4',
            'token' => 'required|string|exists:login_temps,token',
        ];
    }
}
