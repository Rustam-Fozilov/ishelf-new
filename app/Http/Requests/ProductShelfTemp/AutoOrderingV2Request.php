<?php

namespace App\Http\Requests\ProductShelfTemp;

use Illuminate\Foundation\Http\FormRequest;

class AutoOrderingV2Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'shelf_id'       => 'required|integer|exists:shelves,id,status,1',
            'is_promo_bank'  => 'required|integer|in:0,1',
            'order_priority' => 'required|array',
        ];
    }
}
