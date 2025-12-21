<?php

namespace App\Services\Shelf;

use App\Models\Product\Parameter;
use App\Models\Upload;
use App\Models\Shelf\Shelf;
use App\Models\Shelf\PhoneShelf;
use App\Models\PrintLog\PrintLog;
use App\Services\ProductShelf\TvService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\ProductShelfTemp;
use App\Jobs\Shelf\NotifyShelfUpdatedJob;
use App\Services\PrintLog\PrintLogService;
use App\Services\ProductShelf\PhoneService;
use App\Services\RolePerm\PermissionService;

class ShelfService
{
    public function list(array $params): LengthAwarePaginator
    {
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 15;
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $user = auth()->user();
        $perm = PermissionService::getAllow('shelf.list');
        $branch_ids = $user->branches()->pluck('id')->toArray();

        $query = Shelf::query()
            ->with([
                'branches.region',
                'category',
                'phone_tables',
                'last_change.user_info'
            ])
            ->withCount([
                'product_shelf' => function ($q) {
                    $q->whereNull('sku');
                },
                'product_shelf as product_sold_count' => function ($q) {
                    $q->where('is_sold', 1)->whereNotNull('sku');
                }
            ])
            ->where('status', 1)
            ->whereIn('branch_id', $branch_ids)
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
            ->when($perm === 'own', function ($query) use ($user) {
                $category_skus = $user->categories()->pluck('sku')->toArray();

                if ($user->role->category_must_be_added == 1) {
                    $query->whereIn('category_sku', $category_skus);
                }
            })
            ->orderBy($order_by, $order_direction);

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        if ($user->role->title === 'Direktor') {
            $this->addIsNewDirector($result, $user);
        } else {
            $this->addIsNewNonDirector($result);
        }

        return $result;
    }

    private function addIsNewDirector($result, $user): void
    {
        $shelfIds = $result->pluck('id')->toArray();
        $changeIds = $result->whereNotNull('last_change')
            ->pluck('last_change.id')
            ->filter()
            ->toArray();

        if (empty($shelfIds) || empty($changeIds)) {
            $this->addIsNewNonDirector($result);
            return;
        }

        $printLogs = PrintLog::query()
            ->whereIn('shelf_id', $shelfIds)
            ->whereIn('change_id', $changeIds)
            ->where('user_id', $user->id)
            ->where('status', 4)
            ->get()
            ->keyBy(function ($log) {
                return $log->shelf_id . '_' . $log->change_id;
            });

        $result->getCollection()->transform(function ($item) use ($printLogs) {
            if ($item->last_change) {
                $key = $item->id . '_' . $item->last_change->id;
                $item->is_new = !$printLogs->has([$key]);
            } else {
                $item->is_new = false;
            }
            return $item;
        });
    }

    private function addIsNewNonDirector($result): void
    {
        $result->getCollection()->transform(function ($item) {
            $item->is_new = false;
            return $item;
        });
    }

    public function getById(int $id)
    {
        $shelf = Shelf::query()
            ->with([
                'branches',
                'category',
                'phone_tables',
                'upload',
                'phone_tables.phone_shelf_items',
                'auto_ordering'
            ])
            ->findOrFail($id);

        ShelfUpdateService::view($id);
        return $shelf;
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

    public function addV2(array $params)
    {
        DB::beginTransaction();

        try {
            $checkService = new ShelfCheckService();
            $checkService->checkUnique($params['branch_id'], $params['category_sku']);

            $shelf = Shelf::query()->create($params);

            if (!empty($params['items'])) {
                PhoneShelfService::create($shelf->id, $params['items']);
            }

            DB::commit();
            return $shelf;
        } catch (\Throwable $e) {
            return throwResponse($e);
        }
    }

    public function update(int $id, array $params): void
    {
        DB::beginTransaction();

        try {
            $checkService = new ShelfCheckService();
            $checkService->checkUnique($params['branch_id'], $params['category_sku']);
            $is_phone = $checkService->isPhone($params['category_sku']);

            if ($is_phone) {
                PhoneShelfService::create($id, $params['items']);
            }

            Shelf::query()->where('id', $id)->update($params);
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public function deleteSkus(array $data): void
    {
        DB::beginTransaction();

        $region_id = $data['region_id'] ?? null;
        $branch_id = $data['branch_id'] ?? null;
        $category_sku = $data['category_sku'] ?? null;

        $shelves = Shelf::query()
            ->where('status', 1)
            ->when(!is_null($branch_id), function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->when(!is_null($region_id), function ($query) use ($region_id) {
                $query->whereRelation('branches', 'region_id', '=', $region_id);
            })
            ->when(!is_null($category_sku), function ($query) use ($category_sku) {
                $query->where('category_sku', $category_sku);
            })
            ->select('id')
            ->pluck('id');

        try {
            $temps = ProductShelfTemp::query()
                ->whereIn('shelf_id', $shelves)
                ->where('is_sold', 1)
                ->whereNotNull('sold_at')
                ->get();

            foreach ($temps as $temp) {
                if ($temp->product->category_sku == 117) {
                    (new TvService())->deleteTempProduct($temp);
                } else {
                    $temp->sku = null;
                    $temp->sold_at = null;
                    $temp->is_sold = false;
                    $temp->save();
                }
            }

            if ($temps->isNotEmpty()) {
                $shelves->each(function ($shelf_id) {
                    $this::moveToProduct($shelf_id);
                });
            }

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public function delete(int $id): void
    {
        Shelf::query()->where('id', $id)->update(['status' => 0]);
    }

    public function updatePhoneTable(array $params): void
    {
        DB::beginTransaction();

        try {
            $old = PhoneShelf::query()->where('id', $params['phone_table_id'])->first();

            $old->update([
                'size'          => $params['size'] ?? $old->size,
                'type'          => $params['type'],
                'shelf_id'      => $params['shelf_id'],
                'status_zone'   => $params['status_zone'] ?? $old->status_zone,
                'product_count' => $params['product_count'] ?? $old->product_count,
            ]);

            $orderingTemp = ProductShelfTemp::query()
                ->where('shelf_id', $params['shelf_id'])
                ->where('floor', $old->id)
                ->orderBy('ordering')
                ->first();

            self::updatePhoneOrderings($old, $orderingTemp, $params);

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public static function updatePhoneOrderings($phone_shelf, $orderingTemp, $data): void
    {
        ProductShelfTemp::query()
            ->where('shelf_id', $data['shelf_id'])
            ->where('floor', $data['phone_table_id'])
            ->delete();

        ProductShelf::query()
            ->where('shelf_id', $data['shelf_id'])
            ->where('floor', $data['phone_table_id'])
            ->delete();

        $current_ordering = PhoneService::createTempByPhoneShelfId($phone_shelf->id, $orderingTemp->ordering);

        $temp_ordering = $current_ordering;
        $temps = ProductShelfTemp::query()
            ->where('shelf_id', $phone_shelf->shelf_id)
            ->where('floor', '>', $phone_shelf->id)
            ->orderBy('ordering')
            ->get();

        foreach ($temps as $temp) {
            $temp->ordering = $temp_ordering;
            $temp->save();
            $temp_ordering++;
        }

        $shelves = ProductShelf::query()
            ->where('shelf_id', $phone_shelf->shelf_id)
            ->where('floor', '>', $phone_shelf->id)
            ->orderBy('ordering')
            ->get();

        foreach ($shelves as $shelf) {
            $shelf->ordering = $current_ordering;
            $shelf->save();
            $current_ordering++;
        }
    }

    public function moveToProduct(int $shelf_id, ?int $user_id = null): void
    {
        $shelf = Shelf::query()->findOrFail($shelf_id);
        $temp = ProductShelfTemp::query()->where('shelf_id', $shelf_id)->get();
        $change = null;

        if ($shelf->category_sku === 117) {
            $product_shelf = ProductShelf::query()->where('shelf_id', $shelf_id);
            $change = ShelfChangeService::create($shelf, $user_id);

            if ($product_shelf->exists()) {
                $product_shelf->delete();
            }

            foreach ($temp as $item) {
                $this->saveProductShelf($item, $change->id);
            }
        } else {
            foreach ($temp as $item) {
                $check = $this->checkBySkuOrdering($shelf_id, $item->ordering, $item->sku);

                if (!$check) {
                    if (is_null($change)) {
                        $change = ShelfChangeService::create($shelf, $user_id);
                    }

                    $this->deleteProductShelf($shelf_id, $item->ordering);
                    $this->saveProductShelf($item, $change->id);
                }
            }
        }

        if ($change) {
            PrintLogService::create($shelf_id, 2, $user_id);
            dispatch(new NotifyShelfUpdatedJob($shelf));
        }
    }

    protected function saveProductShelf(ProductShelfTemp $item, int $change_id): void
    {
        ProductShelf::query()->create([
            'sku'            => $item->sku,
            'size'           => $item->size,
            'floor'          => $item->floor,
            'place'          => $item->place,
            'sold_at'        => $item->sold_at,
            'is_sold'        => $item->is_sold,
            'ordering'       => $item->ordering,
            'shelf_id'       => $item->shelf_id,
            'change_id'      => $change_id,
            'floor_ordering' => $item->floor_ordering,
        ]);
    }

    public function checkBySkuOrdering(int $shelf_id, int $ordering, ?int $sku = null): ?ProductShelf
    {
        $check = ProductShelf::query()
            ->where('sku', $sku)
            ->where('shelf_id', $shelf_id)
            ->where('ordering', $ordering)
            ->first();

        if (!is_null($sku)) {
            ProductShelf::query()
                ->whereNull('sku')
                ->where('shelf_id', $shelf_id)
                ->where('ordering', $ordering)
                ->delete();
        }

        return $check;
    }

    public function deleteProductShelf(int $shelf_id, int $ordering): void
    {
        ProductShelf::query()
            ->where('shelf_id', $shelf_id)
            ->where('ordering', $ordering)
            ->delete();
    }

    public function orderingProductList(int $shelf_id): array
    {
        $last_change = ShelfChangeService::getLastChange($shelf_id, 'user_info');

        $shelf = ProductShelf::with(['product','product_attr'])
            ->with(['change' => function ($query) use ($last_change) {
                $query->where('id', $last_change->id);
            }])
            ->where('shelf_id', $shelf_id)
            ->get();

        $priority_products = (new ShelfStockPriorityService())->getByShelfId($shelf_id);

        $send['product'] = $shelf;
        $send['change_info'] = $last_change;
        $send['priority_products'] = $priority_products;
        return $send;
    }

    public function uploadImageToPhone(array $params): void
    {
        $upload = Upload::query()->where('url', $params['file_url'])->first();
        Shelf::query()->where('id', $params['shelf_id'])->update(['upload_id' => $upload->id]);
    }

    public function getParameters(array $params)
    {
        $shelf = Shelf::query()->find($params['shelf_id']);

        return Parameter::query()
            ->where('category_sku', $shelf->category_sku)
            ->where('type', 'excel')
            ->select(['key', 'name', 'short_name'])
            ->groupBy(['key', 'name', 'short_name'])
            ->get();
    }
}
