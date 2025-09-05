<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Category\CategoryBrand;
use Illuminate\Http\Request;

class CategoryBrandController extends Controller
{
    public function list(int $sku, Request $request)
    {
        $search = $request['search'] ?? null;
        $search = translit($search);

        $data = CategoryBrand::query()
            ->where('category_sku', $sku)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title','like',"%$search[lat]%")
                        ->orWhere('title','like',"%$search[cyr]%");
                });
            })
            ->paginate($request['per_page'] ?? 15);

        return new Resource($data);
    }
}
