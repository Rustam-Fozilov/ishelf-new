<?php

namespace App\Services\SMS;

use App\Http\Integrations\Anketa\AnketaConnector;
use App\Http\Integrations\Anketa\Requests\SendSmsRequest;
use Saloon\Exceptions\SaloonException;

class SmsService
{
    public static function sendSms(string $phone, string $text): void
    {
        try {
            $token = config('services.anketa.manual_token');
            $request = (new AnketaConnector($token))->send(new SendSmsRequest($phone, $text));

            if ($request->status() != 200) {
                $res = json_decode($request->body(), true);
                throwError($res['errors'][0]['message'] ?? "SMS yuborishda xatolik");
            }
        } catch (SaloonException $e) {
            throwError($e->getMessage());
        }
    }

    public static function smsIsOn(): bool
    {
        return config('services.sms.is_on');
    }

    public static function smsIsSend(): bool
    {
        return config('services.sms.is_send');
    }
}
