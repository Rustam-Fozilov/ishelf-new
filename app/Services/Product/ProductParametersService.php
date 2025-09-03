<?php

namespace App\Services\Product;

use App\Http\Integrations\Idea\IdeaConnector;
use App\Http\Integrations\Idea\Requests\ProductAttributeRequest;
use App\Http\Integrations\Idea\Requests\SearchProductBySku;
use App\Models\ExceptionLog;
use App\Models\Product\Parameter;
use App\Models\Product\Product;

class ProductParametersService
{
    public static function list(array $data)
    {
        $order_by = $data['order_by'] ?? 'ordering';
        $order_direction = $data['order_direction'] ?? 'asc';

        return Parameter::with('icon')
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('category_sku', $data['category_sku']);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                    ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%');
            })
            ->when($order_by === 'key', function ($query) use ($order_direction) {
                $query->orderByRaw("CAST(`key` AS UNSIGNED) $order_direction");
            }, function ($query) use ($order_by, $order_direction) {
                $query->orderBy($order_by, $order_direction);
            })
            ->get();
    }

    public static function getParametersFromIdea(Product $product)
    {
        $connector = new IdeaConnector();

        $response = json_decode($connector->send(new SearchProductBySku($product->sku))->body(), true);
        if (!isset($response['id'])) return ExceptionLog::query()->create(['data' => 'Product not found']);
        $product->update(['url' => $response['url']]);

        $response = json_decode($connector->send(new ProductAttributeRequest($response['id']))->body(), true);
        if (!isset($response['data'])) return ExceptionLog::query()->create(['data' => 'Product attribute not found']);
        $product->parameters()->delete();

        foreach ($response['data'] as $attribute) {
            $param = ProductParametersService::getOrCreateParam($product->category_sku, $attribute['slug'], $attribute['name']);
            $product->parameters()->updateOrCreate(
                ['parameter_id' => $param->id],
                ['value' => $attribute['attribute_values'][0]['name']]
            );
        }

        return true;
    }

    public static function getOrCreateParam(int $category_sku, string $key, string $name = null)
    {
        $param = Parameter::query()->where('key', $key)->where('category_sku', $category_sku)->first();

        if ($param && is_null($param->name) && !is_null($name)) $param->update(['name' => $name]);
        if (!$param) $param = Parameter::query()->create(['key' => $key, 'category_sku' => $category_sku, 'name' => $name]);

        return $param;
    }

    public static function updateParameters(array $data): void
    {
        foreach ($data['parameters'] as $item) {
            $parameter = Parameter::query()->where('category_sku', $data['category_sku'])->where('key', $item['key'])->first();
            $parameter->update([
                'name'       => $item['name'] ?? null,
                'icon_id'    => $item['icon_id'] ?? null,
                'ordering'   => $item['ordering'] ?? null,
                'short_name' => $item['short_name'] ?? null,
            ]);
            $parameter->products()->update(['ordering' => $item['ordering'] ?? null]);
        }
    }
}
