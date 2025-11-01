<?php

namespace App\Http\Requests\PriceTag;

use Illuminate\Foundation\Http\FormRequest;

class AttachTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'template_id'  => 'required|integer|exists:price_tag_templates,id',
            'sennik_ids'   => 'required|array',
            'sennik_ids.*' => 'required|integer|exists:price_tag_senniks,id',
        ];
    }
}
