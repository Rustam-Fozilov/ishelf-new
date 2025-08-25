<?php

namespace App\Services\Shelf;

use App\Http\Requests\Shelf\ListRequest;
use App\Models\Shelf\Shelf;
use Illuminate\Support\Facades\DB;

class ShelfService
{
    public function list(array $params)
    {
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $list = Shelf::query()
            ->with(['branches.region', 'category', 'phone_tables', 'updates', 'user_updates', 'last_change.user_info'])
            ->withCount(['product_shelf' => function ($q) {
                $q->whereNull('sku');
            }])
            ->withCount(['product_shelf as product_sold_count' => function ($q) {
                $q->where('is_sold', 1)->whereNotNull('sku');
            }])
            ->where('status', 1)
            ->when(isset($params['branch_id']), function ($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            })
            ->when(isset($params['region_id']), function ($query) use ($params) {
                $query->whereRelation('branches', 'region_id', '=', $params['region_id']);
            })
            ->when(isset($params['category_sku']), function ($query) use ($params) {
                $query->where('category_sku', $params['category_sku']);
            })
            ->when(isset($params['type']), function ($query) use ($params) {
                $query->where('type', $params['type']);
            })
            ->when(isset($params['floor']), function ($query) use ($params) {
                $query->where('floor', $params['floor']);
            })
            ->when(isset($params['is_paddon']), function ($query) use ($params) {
                $query->where('is_paddon', $params['is_paddon']);
            })
            ->orderBy($order_by, $order_direction)
            ->paginate($params['per_page'] ?? 10);

        return $list->whereIn('branch_id', auth()->user()->branches()->pluck('id')->toArray());
    }

    public function add(array $params)
    {
        DB::beginTransaction();

        try {
            $checkService = new ShelfCheckService();
            $checkService->checkUnique($params['branch_id'], $params['category_sku']);
            $is_phone = $checkService->isPhone($params['category_sku']);

            $shelf = Shelf::query()->create($params);

            if ($is_phone) {
                PhoneShelfService::create($shelf->id, $params['items']);
            }

            DB::commit();
            return $shelf;
        } catch (\Throwable $e) {
            return throwResponse($e);
        }
    }
}
