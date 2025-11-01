<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class AttachBranchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sennik_id'  => 'required|integer|exists:price_tag_senniks,id',
            'branches'   => 'required|array',
            'branches.*' => 'required|integer|exists:branches,id',
        ];
    }
}
