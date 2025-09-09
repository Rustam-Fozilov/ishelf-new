<?php

namespace App\Services\Category;

use App\Models\Category\CategoryAttach;

class CategoryAttachService
{
    public static function getAttachSku(int $category_sku): array
    {
        return CategoryAttach::query()
            ->where('parent_sku', $category_sku)
            ->pluck('child_sku')
            ->toArray();
    }
}
