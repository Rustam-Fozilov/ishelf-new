<?php

namespace App\Services\Product;

use App\Jobs\Product\ProductSyncItemJob;
use App\Jobs\Stock\SendNewStockToBotJob;
use App\Jobs\Stock\SendStockToBotJob;
use App\Models\Branch;
use App\Models\Product\Product;
use App\Models\Product\ProductLog;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\Shelf;
use App\Models\Stock\StockByBranch;
use App\Services\Stock\StockByBranchService;
use Illuminate\Support\Carbon;

class ProductService
{
    public static function syncStock(): void
    {
        $log = ProductLog::query()->orderByDesc('id')->first();

        if ($log) {
            foreach ($log->data['params'] as $item) {
                dispatch(new ProductSyncItemJob($item, $log->id));
            }
        }
    }

    public static function checkProduct(array $product, string $stock_id): void
    {
        $last = (new ProductLogService())->last();

        ProductCategoryService::create(
            (int) $product['categoryID'],
            $product['category'],
            (int) $product['nameID'],
        );

        self::create(
            (int) $product['nameID'],
            (int) $product['categoryID'],
            (int) $product['brandID'],
            $product['name'],
        );

        StockByBranchService::updateOrCreate($stock_id, $product, $last->id);

        $branch_id = Branch::query()->where('token', $stock_id)->value('id');
        $shelves = Shelf::query()
            ->where('branch_id', $branch_id)
            ->where('status', 1)
            ->pluck('id')->toArray();

        if (!empty($shelves)) {
            ProductShelf::query()
                ->where('sku', $product['nameID'])
                ->whereIn('shelf_id', $shelves)
                ->where('is_sold', 1)
                ->update(['is_sold' => 0, 'sold_at' => null]);

            ProductShelfTemp::query()
                ->where('sku', $product['nameID'])
                ->whereIn('shelf_id', $shelves)
                ->where('is_sold', 1)
                ->update(['is_sold' => 0, 'sold_at' => null]);
        }
    }

    public static function deleteOldStockSku(string $stock_id): void
    {
        $last = (new ProductLogService())->last();
        $branch = Branch::query()->where('token', $stock_id)->where('status', 1)->first();

        if ($branch) {
            $stocks = (new StockByBranch())
                ->setTable($branch->token)
                ->newQuery()
                ->where('product_log_id', '!=', $last->id)
                ->pluck('sku')->toArray();

            $shelves = Shelf::query()
                ->where('status', 1)
                ->where('branch_id', $branch->id)
                ->pluck('id')->toArray();

            ProductShelf::query()
                ->whereIn('shelf_id', $shelves)
                ->whereIn('sku', $stocks)
                ->where('is_sold', false)
                ->update([
                    'is_sold' => true,
                    'sold_at' => now(),
                ]);

            ProductShelfTemp::query()
                ->whereIn('shelf_id', $shelves)
                ->whereIn('sku', $stocks)
                ->where('is_sold', false)
                ->update([
                    'is_sold' => true,
                    'sold_at' => now(),
                ]);

            $soldProducts = ProductShelf::with('product.category')
                ->whereIn('shelf_id', $shelves)
                ->whereIn('sku', $stocks)
                ->where('is_sold', true)
                ->distinct('sku');

            $newProducts = (new StockByBranch())
                ->setTable($branch->token)
                ->newQuery()
                ->with('category')
                ->where('product_log_id', $last->id)
                ->where('is_new', true)
                ->get();

            (new StockByBranch())
                ->setTable($branch->token)
                ->newQuery()
                ->where('product_log_id', '!=', $last->id)
                ->delete();

            if ($soldProducts->exists()) {
                $log_date = Carbon::parse($last->created_at)->format('Y-m-d H:i:s');
                dispatch(new SendStockToBotJob($branch, $soldProducts->get()->pluck('product')->toArray(), $log_date));
            }

            if (count($newProducts) > 0) {
                dispatch(new SendNewStockToBotJob($branch, $newProducts->toArray()));
            }
        }
    }

    public static function create(int $sku, int $category_sku, int $brand_sku, string $name)
    {
        return Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'name'         => $name,
                'brand_sku'    => $brand_sku,
                'category_sku' => $category_sku,
            ]
        );
    }

    public function list(array $params)
    {
        $status = $params['status'] ?? null;
        $search = $params['search'] ?? null;

        return Product::with(['attribute', 'child.attribute'])
            ->whereNull('parent_sku')
            ->when(!is_null($status), function ($query) use ($status) {
                $query->whereHas('attribute', function ($query) use ($status) {
                    if ($status == 1) $query->where('status', $status);
                    if ($status == 2) $query->whereIn('status', [2, 3])->orWhereNull('status');
                });
            })
            ->when(isset($params['category_sku']), function ($query) use ($params) {
                $query->where('category_sku', $params['category_sku']);
            })
            ->when(!is_null($search), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . translit($search)['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($search)['lat'] . '%');
                });
            })
            ->paginate($params['per_page'] ?? 10);
    }
}
