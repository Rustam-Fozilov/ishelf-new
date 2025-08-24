<?php

namespace App\Services\Auth;

use App\Models\User;

class AuthService
{
    public function login(array $params): array
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

    public function loginWeb($request)
    {
        $request->validate([
            'phone'    => 'required|regex:/^(998)([0-9]{9})$/',
            'password' => 'required|string|min:6'
        ]);

        $phone = $request->get('phone');
        $password = $request->get('password');

        if (auth()->attempt(['phone' => $phone, 'password' => $password, 'is_admin' => 1])) {
            $request->session()->regenerate();
            return redirect('/telescope');
        }

        return back()->withErrors(['auth' => "Login yoki parol xato!"]);
    }
}
