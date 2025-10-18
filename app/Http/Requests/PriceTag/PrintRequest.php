<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class PrintRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'goods'     => 'required|array',
            'goods.*'   => 'required|integer|exists:products,sku',
            'sennik_id' => 'required|integer|exists:price_tag_senniks,id',
        ];
    }
}
