<?php

namespace App\Http\Requests\PrintLog;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status'   => 'required|integer|in:1,2,4', // 1 => print qilindi | 2 => saqlandi | 4 => ko'rildi,
            'shelf_id' => 'required|integer|exists:shelves,id',
        ];
    }
}
