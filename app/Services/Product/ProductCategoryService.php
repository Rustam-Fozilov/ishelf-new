<?php

namespace App\Services\Product;

use App\Imports\Category\FreezerImport;
use App\Imports\Category\FridgeImport;
use App\Imports\Category\LaptopImport;
use App\Imports\Category\MicrowaveImport;
use App\Imports\Category\MobileImport;
use App\Imports\Category\TvImport;
use App\Jobs\ProductCategory\UploadAttributesJob;
use App\Models\Category\CategoryAttach;
use App\Models\PrintLog\PrintLog;
use App\Models\Product\ProductCategory;
use App\Models\RolePerm\Role;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\Shelf;
use App\Models\Upload;
use App\Services\RolePerm\PermissionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ProductCategoryService
{
    public static function create(int $category_sku, string $title, int $skuname)
    {
        return ProductCategory::query()->updateOrCreate(
            ['sku' => $category_sku],
            [
                'title'   => $title,
                'skuname' => $skuname
            ]
        );
    }

    public static function firstOrCreate(int $category_sku, string $title, int $skuname = null)
    {
        return ProductCategory::query()->firstOrCreate(
            ['sku' => $category_sku],
            [
                'title'   => $title,
                'skuname' => $skuname
            ]
        );
    }

    public function list(array $params): LengthAwarePaginator
    {
        $search = $params['search'] ?? null;

        return ProductCategory::query()
            ->withCount(['filledAttr', 'notFilledAttr', 'allAttr', 'price_tag_goods'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . translit($search)['lat'] . '%')
                        ->orWhere('title', 'like', '%' . translit($search)['cyr'] . '%')
                        ->orWhere('sku', 'like', '%' . translit($search)['lat'] . '%');
                });
            })
            ->when(isset($params['id']), function ($query) use ($params) {
                $query->where('id', $params['id']);
            })
            ->when(isset($params['sku']), function ($query) use ($params) {
                $query->where('sku', $params['sku']);
            })
            ->paginate($params['per_page'] ?? 10);
    }

    public function listPrintType(): Collection
    {
        return ProductCategory::query()
            ->get()
            ->groupBy('print_type')
            ->map(function ($items, $printType) {
                return [
                    'print_type' => !empty($printType) ? $printType : null,
                    'goods' => $items,
                ];
            })
            ->values();
    }

    public function listPriceTag(array $params): Collection
    {
        $search = $params['search'] ?? null;

        $data = ProductCategory::with(['price_tag_goods'])
            ->whereHas('price_tag_goods')
            ->when(!is_null($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . translit($search)['lat'] . '%')
                        ->orWhere('title', 'like', '%' . translit($search)['cyr'] . '%')
                        ->orWhere('sku', 'like', '%' . translit($search)['lat'] . '%');
                });
            })
            ->paginate($params['per_page'] ?? 10);

        return $data->getCollection()->transform(function ($item) {
            $item->goods_count = $item->price_tag_goods->groupBy('category_sku')->count();
            unset($item->price_tag_goods);
            return $item;
        });
    }

    public function show(int $id)
    {
        return ProductCategory::query()->find($id);
    }

    public function addType(array $params): void
    {
        ProductCategory::query()
            ->where('sku', $params['category_sku'])
            ->update(['type' => $params['type']]);
    }

    public function addPrintType(array $params): void
    {
        $data = $params['types'];
        $cats = [];
        array_walk($data, function ($item) use (&$cats) {
            $cats = array_merge($cats, $item['categories'] ?? []);
        });

        if (count($cats) !== count(array_unique($cats))) throwError('Kategoriyalar takrorlanmasligi kerak!');

        foreach ($data as $item) {
            if (empty($item['categories'])) {
                ProductCategory::query()
                    ->where('print_type', $item['print_type'])
                    ->update(['print_type' => null]);
            } else {
                ProductCategory::query()
                    ->whereIn('sku', $item['categories'])
                    ->update(['print_type' => $item['print_type']]);
            }
        }

        ProductCategory::query()
            ->whereNotIn('sku', $cats)
            ->update(['print_type' => null]);
    }

    public function typeList(int $type, int $status = null): array
    {
        $perm = PermissionService::getAllow('shelf.type_list');
        if (!$perm) PermissionService::forbidden('shelf.type_list');

        $role = Role::query()->find(auth()->user()->role_id);
        $branches = auth()->user()->branches()->with(['region'])->where('status', 1)->get();
        $category_skus = auth()->user()->categories()->pluck('sku')->toArray();

        $all_categories = $type == 0 ? ProductCategory::query()->whereIn('type', [1, 2, 3]) : ProductCategory::query()->where('type', $type);
        if ($role->category_must_be_added == 1) $all_categories->whereIn('sku', $category_skus);
        $all_categories = $all_categories->get(['id', 'sku', 'title']);

        /* Parent categorysi borlarni birlashtirib yuborish */
        foreach ($all_categories as $index => $category) {
            $attach = CategoryAttach::query()->where('child_sku', $category->sku)->first();
            if ($attach && $category->sku !== $attach->parent_sku) unset($all_categories[$index]);
        }

        $send = [];
        foreach ($branches as $key => $item) {
            $send[$key]['name'] = $item->name;
            $send[$key]['branch_id'] = $item->id;
            $send[$key]['region'] = $item->region;

            $all_shelves = Shelf::query()
                ->where('branch_id', $item->id)
                ->where('status',1)
                ->with(['last_change.user_info'])
                ->get();

            $collect = collect($all_shelves);
            $new = [];

            foreach ($all_categories as $category) {
                $new[$category->id]['id'] = $category->id;
                $new[$category->id]['sku'] = $category->sku;
                $new[$category->id]['check'] = $collect->where('category_sku',$category->sku)->count();

                if ($new[$category->id]['check'] > 0) {
                    $new[$category->id]['shelf_id'] = ($collect->where('category_sku',$category->sku)->first())->id ?? null;
                    $new[$category->id]['last_change'] = ($collect->where('category_sku',$category->sku)->first())->last_change ?? null;

                    /* Shelf chop etilganligiga va direktor ko'rganligiga tekshirish */
                    $is_printed = false;
                    $is_saw = false;
                    $print_date = null;
                    $saw_date = null;

                    if (!is_null($new[$category->id]['last_change'])) {
                        $shelf = $collect->firstWhere('category_sku', $category->sku);
                        $print_log = PrintLog::query()
                            ->where('shelf_id', $shelf->id)
                            ->where('change_id', $shelf->last_change->id)
                            ->orderByDesc('id');

                        $is_printed = !is_null($print_log->where('status', 1)->first());
                        $last_print = PrintLog::query()
                            ->where('shelf_id', $shelf->id)
                            ->where('status', 1)
                            ->orderByDesc('id')
                            ->first();

                        $last_saw = PrintLog::query()
                            ->where('shelf_id', $shelf->id)
                            ->where('status', 4)
                            ->orderByDesc('id')
                            ->first();

                        $print_date = $last_print->created_at ?? null;
                        $saw_date = $last_saw->created_at ?? null;

                        $is_saw = PrintLog::query()
                            ->where('shelf_id', $shelf->id)
                            ->where('change_id', $shelf->last_change->id)
                            ->where('status', 4)
                            ->exists();
                    }

                    $new[$category->id]['is_printed'] = $is_printed;
                    $new[$category->id]['is_saw'] = $is_saw;
                    $new[$category->id]['print_date'] = $print_date;
                    $new[$category->id]['saw_date'] = $saw_date;

                    /* Shelfda bo'sh joylar borligiga tekshirish */
                    $new[$category->id]['is_dirty'] = false;
                    $product_shelf = ProductShelf::query()
                        ->where('shelf_id', $new[$category->id]['shelf_id'])
                        ->whereNull('sku')
                        ->count();

                    if ($product_shelf > 0) $new[$category->id]['is_dirty'] = true;
                } else {
                    $new[$category->id]['is_saw'] = false;
                    $new[$category->id]['is_printed'] = false;
                    $new[$category->id]['is_dirty'] = false;
                    $new[$category->id]['shelf_id'] = null;
                    $new[$category->id]['print_date'] = null;
                    $new[$category->id]['saw_date'] = null;
                }
            }

            $send[$key]['category'] = array_values($new);
        }

        $data['categories'] = $all_categories->toArray();
        $data['data'] = $send;

        if (!is_null($status)) {
            $data['data'] = array_filter($data['data'], function($branch) use ($status) {
                return !empty($branch['category']) &&
                    array_reduce($branch['category'], function($carry, $category) use ($status) {
                        return $carry && ($category['check'] == $status);
                    }, true);
            });

            $data['data'] = array_values($data['data']);;
            return $data;
        }

        return $data;
    }

    public function uploadAttributes(array $params): void
    {
        dispatch(new UploadAttributesJob($params['upload_id'], $params['category_sku']));
    }

    public static function runAttributeExcel(int $upload_id, int $sku): void
    {
        $upload = Upload::query()->find($upload_id);

        switch ($sku) {
            case 4922: // freezer
                Excel::import(new FreezerImport(), $upload->path);
                break;
//            case 6: // gas cooker
//                Excel::import(new GasImport(), $upload->path);
//                break;
            case 49: // microwave
                Excel::import(new MicrowaveImport(), $upload->path);
                break;
            case 21: // fridge
                Excel::import(new FridgeImport(), $upload->path);
                break;
            case 934: // phone
                Excel::import(new MobileImport(), $upload->path);
                break;
            case 438: // laptop
                Excel::import(new LaptopImport(), $upload->path);
                break;
            case 117: // tv
                Excel::import(new TvImport(), $upload->path);
                break;
            default:
                dd('not supported');
        }
    }
}
