<?php

namespace App\Http\Requests\ProductShelfTemp;

use Illuminate\Foundation\Http\FormRequest;

class AddProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku'      => 'required|integer|exists:products,sku',
            'temp_id'  => 'required|integer|exists:product_shelf_temp,id',
            'shelf_id' => 'required|integer|exists:shelves,id',
            'ordering' => 'required|integer',
        ];
    }
}
