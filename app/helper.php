<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;

function throwErrors($errors, $code = 400)
{
    if (DB::transactionLevel()) DB::rollBack();
    return throw new HttpResponseException(response_errors($errors, $code));
}

function throwError($message, int $code = 400)
{
    return throwErrors(['success'=>false,'message'=>$message],$code);
}

function throwResponse($class, $code = 400)
{
    if (DB::transactionLevel()) DB::rollBack();
    if (method_exists($class, 'getResponse')) throwErrors($class->getResponse()->getData());
    return throwError($class->getMessage(), $code);
}

function failedValidation($validator)
{
    foreach ($validator->errors()->toArray() as $value) {
        throwError($value[0]);
    }
}

function success($data = null): JsonResponse
{
    if ($data === null) $data = ['success' => true];
    return response()->json($data);
}

function response_errors($errors, int $code = 400): JsonResponse
{
    return response()->json($errors, $code);
}

function bindRepo($interface, $repo)
{
    app()->bind($interface, $repo);
    return app()->make($interface);
}

function getPinflYear($n): int
{
    if ($n < 3) {
        // 1800-1899 yildagi 1-erkak, 2-ayol
        return 1800;
    } elseif ($n < 5) {
        // 1900-1999 yildagi 3-erkak, 4-ayol
        return 1900;
    }
    // 2000-2099 yildagi 5-erkak, 6-ayol
    return 2000;
}

function checkPinflNumber(int $pinfl, bool $return_date = false): bool|string
{
    if (!is_int($pinfl)) return false;

    // pinfl 14 raqamdan iborat bo'lishi kerak
    if (strlen($pinfl) !== 14) return false;

    $weigh = "7317317317317";
    $s = (string) $pinfl;

    // boshlang'ich raqam 1, 2, 3, 4, 5, 6 bo'lishi kerak. (2100-yildan keyin bu funksiya o'zgarishi kerak :)
    if ((int )$s[0] > 6) return false;

    $sum = 0;
    for ($i = 0; $i < 13; $i++) {
        $sum += (int)$s[$i] * (int)$weigh[$i];
    }

    // kontrol raqamini tekshirish
    if ($sum % 10 !== (int)$s[13]) return false;

    $day   = $s[1] . $s[2];
    $month = $s[3] . $s[4];
    $year2 = $s[5] . $s[6];
    $year  = getPinflYear((int)$s[0]) + (int)$year2;

    // tug'ilgan sanani tekshirish
    if (!checkdate((int) $month, (int) $day, $year)) return false;

    if ($return_date) return implode('-', [$year, $month, $day]);

    return true;
}


function translit($text, $lang = 'ru')
{
    $data = [
        'ru' => [
            'cyr' => ['А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'],
            'lat' => ['A', 'B', 'V', 'G', 'D', 'E', 'YO', 'J', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'X', 'TS', 'CH', 'SH', 'SH', '', '', '', 'E', 'YU', 'YA']
        ],
        'oz' => [
            'cyr' => ["Ш", "Ч", "Ё", "А", "Б", "Д", "Э", "Ф", "Ғ", "Г", "Ҳ", "И", "Ж", "К", "Л", "М", "Н", "Ў", "О", "П", "Қ", "Р", "С", "Т", "У", "В", "Х", "Й", "З", "Ь"],
            'lat' => ["SH", "CH", "YO", "A", "B", "D", "E", "F", "G'", "G", "H", "I", "J", "K", "L", "M", "N", "O'", "O", "P", "Q", "R", "S", "T", "U", "V", "X", "Y", "Z", "'"]
        ]
    ];
    $text = mb_strtoupper($text);

    $text1 = str_replace($data[$lang]['cyr'], $data[$lang]['lat'], $text);
    $text2 = str_replace($data[$lang]['lat'], $data[$lang]['cyr'], $text);

    return [
        'lat' => $text1,
        'cyr' => $text2
    ];
}

function validateData($data, $rules, $redirect = false)
{
    $validator = Validator::make($data, $rules);

    if ($validator->fails()) {
        if ($redirect) {
            failedValidation($validator);
        } else {
            return false;
        }
    }
    return true;
}

function getLang(): string
{
    $lang = '';
    $url = explode('/', Request::url());
    $url = array_slice($url, 3);

    if (count($url) > 0) {
        $p = @$url[0] == 'api' ? 1 : 0;
        if (in_array(@$url[$p], config('app.locales'))) {
            $lang = '/' . $url[$p];
            app()->setLocale($url[$p]);
        }
    }

    return $lang;
}
