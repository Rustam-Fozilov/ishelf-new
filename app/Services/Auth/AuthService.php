<?php

namespace App\Services\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LoginTemp;
use App\Services\SMS\SmsService;

class AuthService
{
    public function login(array $params): array
    {
        $user = User::query()->where('phone', $params['phone'])->where('status', 1)->first();
        if (!$user) throwError(__('auth.forbidden'));

        if (!auth()->attempt(['phone' => $params['phone'], 'password' => $params['password']])) {
            throwError(__('auth.failed'));
        }

        if (auth()->user()->is_admin == 1) {
            $sms = SmsService::smsIsOn();

            if ($sms) {
                return $this->loginWithSms();
            }

            return $this->loginNoSms();
        }

        return $this->loginNoSms();
    }

    public static function loginWithSms(): array
    {
        if (SmsService::smsIsSend()) {
            $code = generateOtp();
            $text = "ISHELF: Tizimga kirish uchun tasdiqlash kodi: $code";

            SmsService::sendSms(auth()->user()->phone, $text);
        } else {
            $code = 1111;
        }

        $token = generateToken(64);

        LoginTemp::query()->create([
            'otp' => $code,
            'token' => $token,
            'status' => 0,
            'user_id' => auth()->id(),
        ]);

        return [
            'token' => $token,
            'with_sms' => true,
            'expire_at' => now()->addMinutes(2)->format('Y-m-d H:i:s')
        ];
    }

    public function loginNoSms(): array
    {
        $t = $this->generateAuthToken();
        $t['with_sms'] = false;
        return $t;
    }

    public function generateAuthToken():array
    {
        $user = auth()->user();
        $exp_at = Carbon::now()->endOfDay();

        return [
            'token_type' => 'Bearer',
            'expires_at' => $exp_at->format('Y-m-d H:i:s'),
            'access_token' => $user->createToken(generateToken(64), expiresAt: $exp_at)->plainTextToken,
        ];
    }

    public function loginCheckSms(string $token, string $otp): array
    {
        $temp = LoginTemp::query()->where('token', $token)->first();

        $time = Carbon::parse($temp->created_at)->diffInSeconds(Carbon::now());

        if ($time > 120 || $temp->status == 1) {
            throwError(__('errors.otp.expired'));
        }

        if ($temp->otp != $otp) {
            throwError(__('errors.otp.incorrect'));
        }

        $temp->update(['status' => 1]);

        auth()->setUser(User::query()->find($temp->user_id));

        return $this->generateAuthToken();
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
