<?php

namespace App\Services\Shelf;

use App\Models\Shelf\Shelf;
use App\Models\Shelf\ShelvesTemp;
use App\Models\Stock\StockByBranch;
use App\Models\Shelf\ProductShelfTemp;
use App\Models\Shelf\ShelfStockPriority;
use App\Interfaces\ProductShelfInterface;
use App\Services\ProductShelf\TvService;
use App\Services\ProductShelf\PhoneService;
use App\Services\ProductShelf\LaptopService;
use App\Services\ProductShelf\WashingService;
use App\Services\ProductShelf\PrinterService;
use App\Services\ProductShelf\LaptopBagService;
use App\Services\ProductShelf\GasCookersService;
use App\Services\ProductShelf\MicrowavesService;
use App\Services\ProductShelf\ConditionerService;
use App\Services\ProductShelf\WaterHeaterService;
use App\Services\ProductShelf\RefrigeratorService;
use App\Services\ProductShelf\VacuumCleanerService;

class ShelfTempService
{
    public ProductShelfInterface $productService;
    public float $default;
    public float $space;

    public function __construct(
        int $category_sku = null,
        float $default = null,
        float $space = null
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

    public function getTempByShelfId(int $shelf_id)
    {
        $temp = ProductShelfTemp::with(['product','product_attr'])->where('shelf_id', $shelf_id)->get();
        $priority_products = ShelfStockPriority::with(['product', 'product_attr'])->where('shelf_id', $shelf_id)->get();

        foreach ($priority_products as $product) {
            $product->ordering = $product->order;
            unset($product->order);
        }

        if ($temp->isNotEmpty()) {
            foreach ($priority_products as $p) {
                $p->is_priority = true;
                $temp[] = $p;
            }
            $result = $temp;
        } else {
            $this->create($shelf_id);
            $result = ProductShelfTemp::with(['product','product_attr'])->where('shelf_id', $shelf_id)->get();
        }

        return $result;
    }

    public function create(int $shelf_id): void
    {
        $shelf = Shelf::query()->findOrFail($shelf_id);
        $this->productService->createTemp($shelf);
    }

    public function addProduct(array $params): void
    {
        $shelf = Shelf::query()->find($params['shelf_id']);
        $params['shelf'] = $shelf;
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

    public static function tempAddEmptyProduct($shelf_id, $ordering, $place, $floor, $floor_ordering, $size): void
    {
        ProductShelfTemp::query()->create([
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

    public static function checkDublProduct(array $data): void
    {
        $token = $data['shelf']->branches->token;
        $prodCount = ProductShelfTemp::query()->where('shelf_id', $data['shelf']->id)->where('sku', $data['sku'])->count();
        $stockCount = (new StockByBranch())
            ->setTable($token)
            ->newQuery()
            ->where('sku', $data['sku'])
            ->select('quantity')
            ->first();

        if (!is_null($stockCount) && $prodCount >= $stockCount->quantity) {
            throwError(__('errors.out_of_stock'));
        }
    }
}
