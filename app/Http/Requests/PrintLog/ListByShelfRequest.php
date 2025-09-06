<?php

namespace App\Http\Requests\PrintLog;

use Illuminate\Foundation\Http\FormRequest;

class ListByShelfRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status'    => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'per_page'  => 'nullable|integer',
            'page'      => 'nullable|integer',
        ];
    }
}
