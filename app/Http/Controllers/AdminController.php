<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Services\Admin\AdminService;
use App\Jobs\Product\SyncAttributesJob;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $service
    )
    {
    }

    public function branchSync()
    {
        $this->service->branchSync();
        return success();
    }

    public function syncAllParams()
    {
        $products = Product::query()->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function phone()
    {
        $products = Product::query()->where('category_sku','=',934)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function waterHeater()
    {
        $products = Product::query()->where('category_sku', 347)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function refrigerator()
    {
        $products = Product::query()->where('category_sku', 21)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function airConditioner()
    {
        $products = Product::query()->where('category_sku', 59)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function laptop()
    {
        $products = Product::query()->where('category_sku', 438)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function tablet()
    {
        $products = Product::query()->where('category_sku', 935)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function monoBlock()
    {
        $products = Product::query()->where('category_sku', 454)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function printer()
    {
        $products = Product::query()->where('category_sku', 592)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function gasCooker()
    {
        $products = Product::query()->where('category_sku', 6)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function washingMachineSync()
    {
        $products = Product::query()->whereIn('category_sku', [36, 2271])->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function vacuumCleaner()
    {
        $products = Product::query()->where('category_sku', 30)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function tv()
    {
        $products = Product::query()->where('category_sku', 117)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function hood()
    {
        $products = Product::query()->where('category_sku', 55)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function microwaveOven()
    {
        $products = Product::query()->where('category_sku', 49)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function miniOven()
    {
        $products = Product::query()->where('category_sku', 351)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function freezer()
    {
        $products = Product::query()->where('category_sku', 4922)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function oven()
    {
        $products = Product::query()->where('category_sku', 3906)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public function heater()
    {
        $products = Product::query()->where('category_sku', 1142)->get();

        foreach ($products as $product) {
            dispatch(new SyncAttributesJob($product));
        }

        return success();
    }

    public static function syncBySku(int $sku): void
    {
        $product = Product::query()->where('sku', $sku)->first();
        if ($product) dispatch(new SyncAttributesJob($product));
    }
}
