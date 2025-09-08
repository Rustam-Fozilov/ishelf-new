<?php

namespace App\Http\Requests\Shelf;

use Illuminate\Foundation\Http\FormRequest;

class AddRequest extends FormRequest
{
    public function rules(): array
    {
        $type = $this->get('type');

        return [
            'type'                  => 'nullable|integer|in:1,2,3,4,5,6,7,8,9,10', // 1-primoy,2-left,3-п,4-right,5-plisos,6-telefon,7-noutbuk,8-konditioner,9-plisos,10-mikrovolnovka
            'category_sku'          => 'required|integer|exists:product_categories,sku',
            'branch_id'             => 'required|integer|exists:branches,id',
            'product_count'         => $type == 5 ? 'required|integer' : 'nullable|integer',
            'floor'                 => in_array($type, [1, 2, 3, 4, 8]) ? 'required|integer' : 'nullable|integer',
            'floor_left'            => 'nullable|integer|min:1|max:100',
            'floor_right'           => 'nullable|integer|min:1|max:100',
            'size'                  => in_array($type, [1, 2, 3, 4]) ? 'required|integer' : 'nullable|integer',
            'left_size'             => in_array($type, [2, 3]) ? 'required|integer' : 'nullable|integer',
            'right_size'            => in_array($type, [3, 4]) ? 'required|integer' : 'nullable|integer',
            'is_paddon'             => 'required|integer|in:1,0',
            'paddon_quantity'       => 'nullable|required_if:is_paddon,=,1|integer',
            'paddon_front_quantity' => 'nullable|integer',
            'paddon_back_quantity'  => 'nullable|integer',
            'paddon_size'           => 'nullable|integer',

            'items'                 => 'nullable|required_if:type,6|required_if:type,7|required_if:type,9|required_if:type,10|array|min:1',
            'items.*.type'          => 'required|integer|in:1,2,3,4', // 1-pramoy_stol, 2-6_burchak_stol, 3-polka, 4-plesos_stol
            'items.*.size'          => 'nullable|required_if:items.*.type,=,3|integer',
            'items.*.status_zone'   => 'nullable|required_if:type,=,6|string|in:gold,green,red',
            'items.*.product_count' => 'nullable|required_if:items.*.type,=,1,2,4|integer|min:1',
        ];
    }
}
