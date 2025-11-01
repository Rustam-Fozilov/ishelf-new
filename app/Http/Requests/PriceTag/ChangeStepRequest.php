<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStepRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'step'      => 'required|integer',
            'sennik_id' => 'required|integer|exists:price_tag_senniks,id',
        ];
    }
}
