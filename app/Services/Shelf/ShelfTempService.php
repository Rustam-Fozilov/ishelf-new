<?php

namespace App\Services\Shelf;

use App\Models\Branch;
use App\Models\Shelf\Shelf;
use App\Models\Product\Product;
use App\Models\Shelf\ShelvesTemp;
use App\Models\Shelf\AutoOrdering;
use App\Models\Shelf\ProductShelfTemp;
use App\Services\ProductShelf\TvService;
use App\Interfaces\ProductShelfInterface;
use App\Services\ProductShelf\PhoneService;
use App\Services\ProductShelf\LaptopService;
use App\Services\Stock\StockByBranchService;
use Illuminate\Database\Eloquent\Collection;
use App\Services\ProductShelf\WashingService;
use App\Services\ProductShelf\PrinterService;
use App\Services\ProductShelf\LaptopBagService;
use App\Services\ProductShelf\GasCookersService;
use App\Services\ProductShelf\MicrowavesService;
use App\Services\ProductShelf\ConditionerService;
use App\Services\ProductShelf\WaterHeaterService;
use App\Services\ProductShelf\RefrigeratorService;
use App\Services\ProductShelf\VacuumCleanerService;
use Illuminate\Support\Carbon;

class ShelfTempService
{
    public float $space;
    public float $default;
    public ProductShelfInterface $productService;

    public function __construct(
        ?int  $category_sku = null,
        float $default = 0.0,
        float $space = 0.0
    )
    {
        if ($category_sku) {
            $this->setService($category_sku);
        }

        $this->default = $default;
        $this->space = $space;
    }

    public function setService(int $category_sku): void
    {
        switch ($category_sku) {
            case 117:
                $this->productService = new TvService();
                break;
            case 49:
            case 351:
                $this->productService = new MicrowavesService();
                break;
            case 6:
                $this->productService = new GasCookersService();
                break;
            case 36:
            case 2271:
                $this->productService = new WashingService();
                break;
            case 21:
            case 4922:
                $this->productService = new RefrigeratorService();
                break;
            case 59:
                $this->productService = new ConditionerService();
                break;
            case 934:
                $this->productService = new PhoneService();
                break;
            case 438:
                $this->productService = new LaptopService();
                break;
            case 30:
                $this->productService = new VacuumCleanerService();
                break;
            case 592:
            case 596:
                $this->productService = new PrinterService();
                break;
            case 347:
                $this->productService = new WaterHeaterService();
                break;
            case 1181:
                $this->productService = new LaptopBagService();
                break;
        }
    }

    public function create(int $shelf_id): void
    {
        $shelf = Shelf::query()->findOrFail($shelf_id);
        $this->productService->createTemp($shelf);
    }

    public function getTempByShelfId(int $shelf_id): Collection
    {
        $temp = ProductShelfTemp::with(['product','product_attr'])->where('shelf_id', $shelf_id)->get();
        $priority_products = (new ShelfStockPriorityService())->getByShelfId($shelf_id); // prioritetdagi tovarlar

        return $this->getOrCreate($temp, $priority_products, $shelf_id);
    }

    public function getOrCreate($product_shelf_temp, $priority_products, int $shelf_id): Collection
    {
        if ($product_shelf_temp->isNotEmpty()) {
            foreach ($priority_products as $p) {
                $p->is_priority = true;
                $product_shelf_temp[] = $p;
            }

            $result = $product_shelf_temp;
        } else {
            $this->create($shelf_id);
            $result = ProductShelfTemp::with(['product', 'product_attr'])->where('shelf_id', $shelf_id)->get();
        }

        return $result;
    }

    public function addProduct(array $params): void
    {
        $params['shelf'] = Shelf::query()->find($params['shelf_id']);
        $this->productService->tempAddProduct($params);
    }

    public function dialProduct($shelf_id, $floor, $place, $length, $ordering, $floor_ordering, $add_temp = true)
    {
        $i = 0;
        $default = $this->default;
        $space = $this->space;

        while ($length >= $default) {
            $i++;
            $length = $floor_ordering == 1 ? $length - $default : $length - ($default + $space);
            if ($length < 0) break;

            $this::tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $i, $default);
            $ordering++;
            $floor_ordering++;
        }

        if ($add_temp) {
            $this->addShelfTemp($shelf_id, $place, $floor, $length);
        }

        return $ordering;
    }

    public static function tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $floor_ordering, $size)
    {
        return ProductShelfTemp::query()->create([
            'size'           => $size,
            'place'          => $place,
            'floor'          => $floor,
            'is_sold'        => false,
            'sold_at'        => null,
            'shelf_id'       => $shelf_id,
            'ordering'       => $ordering,
            'floor_ordering' => $floor_ordering,
        ]);
    }

    public function addShelfTemp($shelf_id, $place, $floor, $length): void
    {
        $check = ShelvesTemp::query()
            ->where('shelf_id', $shelf_id)
            ->where('place', $place)
            ->where('floor', $floor)
            ->first();

        if (is_null($check)) {
            $check = ShelvesTemp::query()->create([
                'shelf_id' => $shelf_id,
                'place'    => $place,
                'floor'    => $floor,
            ]);
        }

        $check->excess = $length;
        $check->save();
    }

    public function deleteTempProduct(int $temp_id): void
    {
        $temp = ProductShelfTemp::query()->findOrFail($temp_id);
        $this->productService->deleteTempProduct($temp);
    }

    public function autoOrdering(array $params): Collection
    {
        $shelf = Shelf::query()->where('status', 1)->find($params['shelf_id']);
        return $this->productService->tempAutoOrderProduct($shelf, $params['order_priority']);
    }

    public static function getStocksForShelf(Shelf $shelf)
    {
        $branch = Branch::query()->where('id', $shelf->branch_id)->first();
        $stock = (new StockByBranchService())->getStock($branch->token, $shelf->category_sku);

        return Product::query()
            ->join('product_attributes', 'products.sku', '=', 'product_attributes.sku')
            ->whereNull('products.parent_sku')
            ->where('products.status', 1)
            ->whereIn('products.sku', $stock->pluck('sku')->toArray())
            ->distinct('products.sku');
    }

    public function saveAutoOrderingProps(array $data): void
    {
        $shelf = Shelf::query()->where('id', $data['shelf_id'])->first();
        $orderings = $data['order_priority'];

        if ($shelf->category_sku != 934) {
            $orderings = collect($data['order_priority'])->transform(function ($item) {
                return [$item['order_by'] => $item['order_direction']];
            });
        }

        AutoOrdering::query()->updateOrCreate(
            ['shelf_id' => $data['shelf_id']],
            ['order_by' => json_encode($orderings)]
        );
    }

    public function deleteAutoOrderingProps(int $shelf_id): void
    {
        AutoOrdering::query()->where('shelf_id', $shelf_id)->delete();
    }

    public function makeAutoOrdering(): void
    {
        $shelves = AutoOrdering::with(['shelf'])
            ->whereRelation('shelf', 'status', '=', 1)
            ->whereRelation('shelf', 'branch_id', '=', 5)
            ->get();

        foreach ($shelves as $shelf) {
            $branch = Branch::query()->where('id', $shelf->shelf->branch_id)->where('status', 1)->first();
            $end_work = $branch->end_of_work ?? "21:00";
            $expire = Carbon::parse($end_work)->subMinutes(30);

            if (Carbon::now()->greaterThanOrEqualTo($expire)) {
                $this::autoOrdering(['shelf_id' => $shelf->shelf_id, 'order_priority' => $shelf->order_by]);
                (new ShelfService())->moveToProduct($shelf->shelf_id, 1);
            }
        }
    }
}
