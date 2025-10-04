<?php

namespace App\Http\Requests\Shelf;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhoneImageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shelf_id' => 'required|integer|exists:shelves,id,category_sku,934',
            'file_url' => 'required|string',
        ];
    }
}
