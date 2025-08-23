<?php

namespace App\Services\User;

use App\Models\UserCategories;

class UserCategoriesService
{
    public static function create(int $user_id, array $categories): void
    {
        UserCategories::query()->where('user_id', $user_id)->delete();

        foreach ($categories as $sku) {
            UserCategories::query()->create([
                'user_id'      => $user_id,
                'category_sku' => $sku
            ]);
        }
    }
}
