<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function rules(): array
    {
        $route = request()->route()->getName();

        if ($route == 'upload.image') {
            $rule = 'max:10240|image';
        } else if ($route == 'upload.excel') {
            $rule = 'mimes:xlsx,csv';
        } else {
            $rule = 'max:10240';
        }

        return [
            'file' => 'required|file|' . $rule
        ];
    }
}
