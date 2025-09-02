<?php

namespace App\Http\Requests\Shelf;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneTableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shelf_id'       => 'required|integer|exists:shelves,id',
            'phone_table_id' => 'required|integer|exists:phone_shelves,id',
            'type'           => 'required|integer|in:1,2,3,4',
            'status_zone'    => 'nullable|required_if:type,=,6|string|in:gold,green,red',
            'product_count'  => 'nullable|required_if:type,=,1,2,4|integer',
            'size'           => 'nullable|required_if:type,=,3|integer',
        ];
    }
}
