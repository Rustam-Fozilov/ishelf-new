<?php

namespace App\Services\Auth;

use App\Models\User;

class AuthService
{
    public static function login(array $params): array
    {
        $user = User::query()->where('phone', $params['phone'])->where('status', 1)->first();
        if (!$user) throwError(__('auth.failed'));

        if (!auth()->attempt(['phone' => $params['phone'], 'password' => $params['password']])) {
            throwError(__('auth.failed'));
        }

        $token = $user->createToken('auth_token', expiresAt: today()->endOfDay())->plainTextToken;

        return [
            'token_type'   => 'Bearer',
            'access_token' => $token,
            'expires_at'   => today()->endOfDay()->toDateTimeString(),
        ];
    }
}
