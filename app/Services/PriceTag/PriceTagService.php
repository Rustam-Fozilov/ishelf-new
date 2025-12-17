<?php

namespace App\Services\PriceTag;

use App\Jobs\PriceTag\MoveSennikJob;
use App\Jobs\PriceTag\NotifySennikJob;
use App\Jobs\PriceTag\ProcessPriceTagPrintJob;
use App\Models\Branch;
use App\Models\PriceTag\PriceTagGood;
use App\Models\PriceTag\PriceTagGoodTemp;
use App\Models\PriceTag\PriceTagPrints;
use App\Models\PriceTag\PriceTagTemplate;
use App\Models\PriceTag\Sennik;
use App\Models\PriceTag\SennikTemp;
use App\Models\Product\ProductMonth;
use App\Models\Shelf\ProductShelf;
use App\Models\Shelf\Shelf;
use App\Models\Stock\StockByBranch;
use App\Models\User;
use App\Models\User\UserBranch;
use App\Services\RolePerm\PermissionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PriceTagService
{
    public function list(array $params): LengthAwarePaginator
    {
        $query = PriceTagGood::with(['product.parameters', 'product.brand', 'product.category', 'months'])
            ->whereRelation('product.category', 'sku', '=', $params['category_sku'])
            ->whereHas('product.parameters')
            ->get()
            ->groupBy('sku')
            ->map(function ($items, $sku) {
                return [
                    'sku' => $sku,
                    'name' => $items->first()->product->name,
                    'url' => $items->first()->product->url,
                    'price' => $items->first()->product->price,
                    'category' => $items->first()->product->category,
                    'brand' => $items->first()->product->brand,
                    'parameters' => $items->first()->product->parameters,
                    'prices' => $items->first()->months->map(function ($item) use ($items) {
                        return [
                            'bonus' => $item->bonus,
                            'bonus_month' => $item->month,
                            'remove_price' => $items->first()->product->price / $item->month,
                            'start_date' => $items->first()->start_date,
                            'end_date' => $items->first()->end_date,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $perPage = $params['per_page'] ?? 15;
        $currentPage = $params['page'] ?? 1;
        return new LengthAwarePaginator(
            $query->forPage($currentPage, $perPage)->values(),
            $query->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );
    }

    public function print(array $params): void
    {
        foreach($params['goods'] as $sku) {
            PriceTagPrints::query()->create([
                'user_id' => auth()->id(),
                'sku' => $sku,
                'sennik_id' => $params['sennik_id'],
                'type' => 'print',
            ]);
        }
    }

    public function analyticList(array $params)
    {
        $status = $params['status'] ?? 1;
        $id = $params['sennik_id'] ?? Sennik::query()->orderByDesc('id')->first()->id;

        $senniks = Sennik::with(['goods', 'template'])->where('id', $id)->where('status', $status)->get();
        $branches = Branch::with(['region'])->where('status', 1)->get();

        $branches->map(function ($branch) use ($senniks) {
            $sennikBranches = $senniks->map(function ($sennik) use ($branch) {
                $skus = $sennik->goods->pluck('sku')->toArray();
                unset($sennik->goods);

                $stock_count = (new StockByBranch())
                    ->setTable($branch->token)
                    ->newQuery()
                    ->whereIn('sku', $skus)
                    ->count();

                $shelf_count = ProductShelf::query()
                    ->whereHas('shelves', function ($query) use ($branch) {
                        $query->where('status', 1)->where('branch_id', $branch->id);
                    })
                    ->whereIn('sku', $skus)
                    ->distinct('sku')
                    ->count();

                $users = UserBranch::query()->where('branch_id', $branch->id)->pluck('user_id')->toArray();
                $prints = PriceTagPrints::query()
                    ->whereIn('user_id', $users)
                    ->where('sennik_id', $sennik->id)
                    ->where('type', 'print')
                    ->whereIn('sku', $skus)
                    ->distinct('sku')
                    ->count();

                $sennikCopy = clone $sennik;
                $sennikCopy->sennik_count = count($skus);
                $sennikCopy->stock_count = $stock_count;
                $sennikCopy->sh_count = $shelf_count;
                $sennikCopy->prints = $prints;
                return $sennikCopy;
            });

            $branch->senniks = $sennikBranches;
            return $branch;
        });

        return $branches;
    }

    public function analyticByBranchSennik(array $params, int $branch_id, int $sennik_id): array
    {
        $sennik = Sennik::with('template')->find($sennik_id);
        $branch = Branch::with(['region'])->find($branch_id);
        $goods = PriceTagGood::with(['product.category'])->where('sennik_id', $sennik_id)->get();
        $skus = $goods->pluck('sku')->toArray();

        $stock_count = (new StockByBranch())
            ->setTable($branch->token)
            ->newQuery()
            ->whereIn('sku', $skus)
            ->select('sku', 'quantity')
            ->get()
            ->keyBy('sku');

        $shelf_count = ProductShelf::query()
            ->whereHas('shelves', function ($query) use ($branch) {
                $query->where('status', 1)->where('branch_id', $branch->id);
            })
            ->whereIn('sku', $skus)
            ->select('sku')
            ->get()
            ->groupBy('sku')
            ->map(fn ($group) => $group->count());

        $users = UserBranch::query()->where('branch_id', $branch->id)->pluck('user_id')->toArray();
        $printDates = PriceTagPrints::query()
            ->whereIn('sku', $skus)
            ->where('sennik_id', $sennik_id)
            ->where('type', 'print')
            ->whereIn('user_id', $users)
            ->select('sku', 'created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('sku')
            ->map(fn ($group) => $group->first()->created_at->format('Y-m-d H:i:s'));

        $goods->transform(function ($good) use ($stock_count, $shelf_count, $printDates) {
            $sku = $good->sku;
            $good->stock_count = $stock_count[$sku]->quantity ?? 0;
            $good->shelf_count = $shelf_count[$sku] ?? 0;
            $good->print_date = $printDates[$sku] ?? null;

            return $good;
        });

        return [
            'sennik' => $sennik,
            'branch' => $branch,
            'goods' => $goods,
        ];
    }

    public function sennikList(array $params): LengthAwarePaginator
    {
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $list = Sennik::with(['template'])
            ->when(isset($params['status']), function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when(isset($params['search']), function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->where('name', 'like', '%' . $params['search'] . '%')
                        ->orWhere('log_id', 'like', $params['search']);
                });
            })
            ->when(isset($params['sku']), function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->whereHas('goods', function ($query) use ($params) {
                        $query->where('sku', $params['sku']);
                    })
                        ->orWhereHas('goods.product', function ($query) use ($params) {
                            $query->where('name', 'like', $params['sku']);
                        });
                });
            })
            ->when(isset($params['has_template']) && $params['has_template'] == 1, function ($query) {
                $query->whereNotNull('template_id');
            })
            ->when(isset($params['has_template']) && $params['has_template'] == 0, function ($query) {
                $query->whereNull('template_id');
            })
            ->where(function ($q) {
                if (auth()->user()->is_admin == 1) return;
                $userBranchIds = auth()->user()->branches()->pluck('branch_id')->toArray();

                if (!empty($userBranchIds)) {
                    $q->whereDoesntHave('branch')
                        ->orWhereHas('branch', function ($sub) use ($userBranchIds) {
                            $sub->whereIn('id', $userBranchIds);
                        });
                }
            })
            ->orderBy($order_by, $order_direction)
            ->paginate($params['per_page'] ?? 15);

        if (isset($params['sku']) && $list->count() > 1) {
            $list->setCollection($list->getCollection()->sortByDesc('id')->take(1)->values());
        }

        $user_branches = auth()->user()->branches()->pluck('token')->toArray();
        $list->getCollection()->transform(function ($sennik) use ($user_branches) {
            $good_count = $sennik->goods->count();
            $skus = $sennik->goods->pluck('sku')->toArray();
            $is_saw = PriceTagPrints::query()->where('sennik_id', $sennik->id)->where('type', 'see')->where('user_id', auth()->id())->exists();
            $stock_count = [];

            foreach ($user_branches as $branch) {
                (new StockByBranch())->setTable($branch)
                    ->newQuery()
                    ->whereIn('sku', $skus)
                    ->pluck('sku')
                    ->each(function ($sku) use (&$stock_count) {
                        if (!in_array($sku, $stock_count)) $stock_count[] = $sku;
                    });
            }

            $sennik->shelf_count = count($stock_count);
            $sennik->good_count = $good_count;
            $sennik->is_saw = $is_saw;
            unset($sennik->goods);
            return $sennik;
        });

        return $list;
    }

    public function sennikTempList(array $params)
    {
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $list = SennikTemp::with(['template'])
            ->when(isset($params['search']), function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->where('name', 'like', '%' . $params['search'] . '%')
                        ->orWhere('log_id', 'like', $params['search']);
                });
            })
            ->when(isset($params['sku']), function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->whereHas('goods', function ($query) use ($params) {
                        $query->where('sku', $params['sku']);
                    })
                        ->orWhereHas('goods.product', function ($query) use ($params) {
                            $query->where('name', 'like', $params['sku']);
                        });
                });
            })
            ->when(isset($params['has_template']) && $params['has_template'] == 1, function ($query) {
                $query->whereNotNull('template_id');
            })
            ->when(isset($params['has_template']) && $params['has_template'] == 0, function ($query) {
                $query->whereNull('template_id');
            })
            ->orderBy($order_by, $order_direction)
            ->paginate($params['per_page'] ?? 15);

        $list->getCollection()->transform(function ($sennik) {
            $good_count = $sennik->goods->count();
            $skus = $sennik->goods->pluck('sku')->toArray();
            $user_branches = auth()->user()->branches()->pluck('token')->toArray();
            $stock_count = [];

            foreach ($user_branches as $branch) {
                (new StockByBranch())->setTable($branch)
                    ->newQuery()
                    ->whereIn('sku', $skus)
                    ->pluck('sku')
                    ->each(function ($sku) use (&$stock_count) {
                        if (!in_array($sku, $stock_count)) $stock_count[] = $sku;
                    });
            }

            $sennik->shelf_count = count($stock_count);
            $sennik->good_count = $good_count;
            $sennik->is_saw = 1;
            unset($sennik->goods);
            return $sennik;
        });

        return $list;
    }

    public function sennikSelect(array $params)
    {
        return Sennik::with(['template'])->where('status', 1)->select('id', 'name')->paginate($params['per_page'] ?? 15);
    }

    public function sennikShow(int $id, array $data)
    {
        $order_by = $data['order_by'] ?? 'price_tag_goods.id';
        $order_direction = $data['order_direction'] ?? 'asc';
        if (!str_contains($order_by, 'price_tag_goods')) $order_by = 'price_tag_goods.' . $order_by;

        $sennik = Sennik::with(['template'])->find($id);
        if (!$sennik) throwError('sennik not found');

        $goods = PriceTagGood::with(['months', 'months_info', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->where('price_tag_goods.sennik_id', $id)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('price_tag_goods.category_sku', $data['category_sku']);
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('price_tag_goods.sku', $data['skus']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_goods.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_goods.sku', array_unique($result));
                }
            });

        if ($order_by === 'price_tag_goods.print_type') {
            $goods
                ->join('products', 'price_tag_goods.sku', '=', 'products.sku')
                ->join('product_categories', 'products.category_sku', '=', 'product_categories.sku')
                ->orderBy('product_categories.print_type', $order_direction)
                ->select('price_tag_goods.*');
        } else {
            $goods->orderBy($order_by, $order_direction);
        }

        $goods = $this->getWithPerm($goods)->get();
        $goods = $this->checkExistsOnShelf($goods);

        dispatch(new ProcessPriceTagPrintJob(auth()->id(), $id, 'see'));
        return $sennik->setRelation('goods', $goods);
    }

    public function sennikShowTemp(int $id, array $data)
    {
        $order_by = $data['order_by'] ?? 'price_tag_good_temp.id';
        $order_direction = $data['order_direction'] ?? 'asc';
        if (!str_contains($order_by, 'price_tag_goods')) $order_by = 'price_tag_good_temp.' . $order_by;

        $sennik = SennikTemp::with(['template'])->find($id);
        if (!$sennik) throwError('sennik not found');

        $goods = PriceTagGoodTemp::with(['months', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->where('price_tag_good_temp.sennik_id', $id)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('price_tag_good_temp.category_sku', $data['category_sku']);
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('price_tag_good_temp.sku', $data['skus']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_good_temp.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_good_temp.sku', array_unique($result));
                }
            });

        if ($order_by === 'price_tag_goods.print_type') {
            $goods
                ->join('products', 'price_tag_goods.sku', '=', 'products.sku')
                ->join('product_categories', 'products.category_sku', '=', 'product_categories.sku')
                ->orderBy('product_categories.print_type', $order_direction)
                ->select('price_tag_goods.*');
        } else {
            $goods->orderBy($order_by, $order_direction);
        }

        $goods = $goods->get();
        $goods = $this->checkExistsOnShelf($goods);

        return $sennik->setRelation('goods', $goods);
    }

    public static function getWithPerm($list, $return_skus = false)
    {
        if (auth()->user()->is_admin == 0) {
            $perm = PermissionService::getAllow('priceTag.list');

            if ($perm == 'own') {
                $skus = [];
                $branch_ids = auth()->user()->branches()->pluck('token')->unique()->toArray();

                foreach ($branch_ids as $branch_id) {
                    $result = (new StockByBranch())->setTable($branch_id)->newQuery()->pluck('sku')->toArray();
                    $skus = array_merge($skus, $result);
                }

                $skus = array_unique($skus);
                if ($return_skus) return $skus;
                $list->whereIn('price_tag_goods.sku', $skus);
            }
        }

        return $list;
    }

    public function checkExistsOnShelf($goods, bool $mapping = true)
    {
        $user_id = auth()->id();
        $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
            return auth()->user()->branches()->pluck('branch_id')->toArray();
        });
        $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
            return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
        });

        if (is_numeric($goods)) {
            return ProductShelf::query()
                ->whereIn('shelf_id', function ($query) use ($user_branches) {
                    $query->select('id')->from('shelves')->whereIn('branch_id', $user_branches);
                })
                ->where('sku', $goods)
                ->exists();
        }

        $skus = $goods->pluck('sku')->toArray();
        if (!empty($skus)) {
            $exists_on_shelf = Cache::remember("user_{$user_id}_{$goods[0]->sennik_id}", now()->addHours(1), function () use ($shelves, $skus) {
                return ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->whereIn('sku', $skus)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();
            });
            if (!$mapping) return $exists_on_shelf;
        } else {
            $exists_on_shelf = [];
        }

        $exists_map = array_fill_keys($exists_on_shelf, true);
        $goods->transform(function ($good) use ($exists_map) {
            $good->on_shelf = isset($exists_map[$good->sku]);
            return $good;
        });

        return $goods;
    }

    public function sennikAttachTemplate(array $data): void
    {
        Sennik::query()
            ->whereIn('id', $data['sennik_ids'])
            ->update(['template_id' => $data['template_id'], 'status' => 1]);

        $users = User::query()
            ->where('role_id', [1, 2])
            ->where('status', 1)
            ->whereNotNull('telegraph_chat_id')
            ->get();

        foreach ($users as $user) {
            dispatch(new NotifySennikJob($user->id, $data['sennik_ids']));
        }
    }

    public function sennikCheckAmount(int $sennik_id): array
    {
        $sennik = Sennik::with(['goods.product' => ['category', 'brand']])->find($sennik_id);
        $dirty_skus = [];

        foreach ($sennik->goods as $good) {
            $file_months = ProductMonth::query()->where('sku', $good->sku)->get();
            foreach ($good->months as $month) {
                $file_month = $file_months->where('month', $month->month)->first();
                if ($file_month && $month->price >= $file_month->price) {
                    $dirty_skus[] = [
                        'sku' => $good->sku,
                        'product' => $good->product,
                        'month' => $month->month,
                        'price' => $month->price,
                        'file_price' => $file_month->price
                    ];
                }
            }
        }

        return [
            'dirty' => !empty($dirty_skus),
            'skus' => $dirty_skus
        ];
    }

    public function sennikChangeStep(array $params): void
    {
        $sennik = Sennik::query()->find($params['sennik_id']);
        $step = $params['step'];
        $sennik->step = $step;
        if ($params['step'] == 2) $sennik->status = 1;
        $sennik->save();
    }

    public function sennikDelete(int $id): void
    {
        $sennik = Sennik::query()->find($id);
        $sennik->status = 0;
        $sennik->save();

        dispatch(new MoveSennikJob($sennik->id));
    }

    public function attachBranch(array $data): void
    {
        $sennik = Sennik::query()->find($data['sennik_id']);
        $sennik->branches()->sync($data['branches']);
    }

    public function groupByCategoryList(int $id, array $data)
    {
        $list = Sennik::query()
            ->join('price_tag_goods', 'price_tag_goods.sennik_id', '=', 'price_tag_senniks.id')
            ->join('product_categories', 'product_categories.sku', '=', 'price_tag_goods.category_sku')
            ->where('price_tag_senniks.id', $id)
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('price_tag_goods.category_sku', $data['category_sku']);
            })
            ->select([
                'price_tag_senniks.name as sennik_name',
                'price_tag_goods.category_sku',
                'product_categories.title',
                DB::raw('count(price_tag_goods.category_sku) as count')
            ])
            ->groupBy('price_tag_goods.category_sku', 'price_tag_senniks.name');

        $skus = $this->getWithPerm($list, true);
        $perm = PermissionService::getAllow('priceTag.list');
        if (auth()->user()->is_admin == 0 && $perm == 'own') $list->whereIn('price_tag_goods.sku', $skus);

        return $list
            ->groupBy('price_tag_goods.category_sku', 'price_tag_senniks.name')
            ->get();
    }

    public function groupByCategoryShow(int $sennik_id, $sku, array $data)
    {
        $goods = PriceTagGood::with(['months', 'months_info', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->where('sennik_id', $sennik_id)
            ->where('category_sku', $sku)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('sku', $data['skus']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_goods.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_goods.sku', array_unique($result));
                }
            });

        $goods = $this->getWithPerm($goods)->get();
        return $this->checkExistsOnShelf($goods);
    }

    public function groupByPrintTypeList(int $id, array $data)
    {
        $list = Sennik::query()
            ->join('price_tag_goods', 'price_tag_goods.sennik_id', '=', 'price_tag_senniks.id')
            ->join('product_categories', 'product_categories.sku', '=', 'price_tag_goods.category_sku')
            ->where('price_tag_senniks.id', $id)
            ->whereNotNull('product_categories.print_type')
            ->select([
                'price_tag_senniks.id as sennik_id',
                'price_tag_senniks.name as sennik_name',
                'product_categories.print_type',
                DB::raw('count(product_categories.print_type) as count')
            ]);

        $skus = $this->getWithPerm($list, true);
        $perm = PermissionService::getAllow('priceTag.list');
        if (auth()->user()->is_admin == 0 && $perm == 'own') $list->whereIn('price_tag_goods.sku', $skus);

        return $list
            ->groupBy('product_categories.print_type', 'price_tag_senniks.name', 'price_tag_senniks.id')
            ->get();
    }

    public function groupByPrintTypeShow(int $sennik_id, string $type, array $data)
    {
        $goods = PriceTagGood::with(['months', 'months_info', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->where('sennik_id', $sennik_id)
            ->whereHas('product.category', function ($query) use ($type) {
                $query->where('print_type', $type);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('sku', $data['skus']);
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('category_sku', $data['category_sku']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_goods.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_goods.sku', array_unique($result));
                }
            });

        $goods = $this->getWithPerm($goods)->get();
        return $this->checkExistsOnShelf($goods);
    }

    public function groupByPrintedList(int $sennik_id, array $data): array
    {
        $order_by = $data['order_by'] ?? 'price_tag_goods.id';
        $order_direction = $data['order'] ?? 'desc';

        $template = Sennik::query()->find($sennik_id)->template;
        $goods = PriceTagGood::with(['months', 'months_info', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->join('price_tag_prints', function ($query) {
                $query->on('price_tag_prints.sku', '=', 'price_tag_goods.sku')
                    ->where('price_tag_prints.type', 'print')
                    ->where('price_tag_prints.user_id', auth()->id());
            })
            ->join('product_categories', 'product_categories.sku', '=', 'price_tag_goods.category_sku')
            ->where('price_tag_prints.sennik_id', $sennik_id)
            ->where('price_tag_goods.sennik_id', $sennik_id)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('category_sku', $data['category_sku']);
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('price_tag_goods.sku', $data['skus']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_goods.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_goods.sku', array_unique($result));
                }
            })
            ->select('price_tag_goods.*', 'product_categories.print_type')
            ->distinct('price_tag_goods.sku')
            ->orderBy($order_by, $order_direction);

        $goods = $this->getWithPerm($goods)->get();
        $goods = $this->checkExistsOnShelf($goods);

        return [
            'template' => $template,
            'goods' => $goods
        ];
    }

    public function groupByUnPrintedList(int $sennik_id, array $data): array
    {
        $order_by = $data['order_by'] ?? 'price_tag_goods.id';
        $order_direction = $data['order'] ?? 'desc';

        $template = Sennik::query()->find($sennik_id)->template;
        $goods = PriceTagGood::with(['months', 'months_info', 'product.category', 'product.brand', 'product.parameters.parameter'])
            ->join('product_categories', 'product_categories.sku', '=', 'price_tag_goods.category_sku')
            ->where('price_tag_goods.sennik_id', $sennik_id)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereHas('product', function ($query) use ($data) {
                    $query->where('name', 'like', '%' . translit($data['search'])['lat'] . '%')
                        ->orWhere('name', 'like', '%' . translit($data['search'])['cyr'] . '%')
                        ->orWhere('sku', $data['search']);
                });
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('price_tag_goods.category_sku', $data['category_sku']);
            })
            ->whereNotIn('price_tag_goods.sku', function ($query) use ($sennik_id) {
                $query
                    ->select('sku')
                    ->from('price_tag_prints')
                    ->where('price_tag_prints.user_id', auth()->id())
                    ->where('price_tag_prints.type', 'print')
                    ->where('price_tag_prints.sennik_id', $sennik_id);
            })
            ->when(!empty($data['skus']), function ($query) use ($data) {
                $query->whereIn('price_tag_goods.sku', $data['skus']);
            })
            ->when(isset($data['only_shelf']), function ($query) use ($data) {
                $user_id = auth()->id();
                $user_branches = Cache::remember("user_{$user_id}_branches", now()->addHours(1), function () {
                    return auth()->user()->branches()->pluck('branch_id')->toArray();
                });
                $shelves = Cache::remember("user_{$user_id}_shelves", now()->addHours(1), function () use ($user_branches) {
                    return Shelf::query()->whereIn('branch_id', $user_branches)->pluck('id')->toArray();
                });
                $result = ProductShelf::query()
                    ->whereIn('shelf_id', $shelves)
                    ->where('sku', '!=', null)
                    ->distinct('sku')
                    ->pluck('sku')
                    ->toArray();

                if ($data['only_shelf'] == 1) {
                    $query->whereIn('price_tag_goods.sku', array_unique($result));
                } else {
                    $query->whereNotIn('price_tag_goods.sku', array_unique($result));
                }
            })
            ->select('price_tag_goods.*', 'product_categories.print_type')
            ->distinct('price_tag_goods.sku')
            ->orderBy($order_by, $order_direction);

        $goods = $this->getWithPerm($goods)->get();
        $goods = $this->checkExistsOnShelf($goods);

        return [
            'template' => $template,
            'goods' => $goods
        ];
    }

    public function listTemplate(array $data): LengthAwarePaginator
    {
        $order_by = $data['order_by'] ?? 'id';
        $order_direction = $data['order'] ?? 'desc';

        return PriceTagTemplate::with('senniks')
            ->orderBy($order_by, $order_direction)
            ->paginate($data['per_page'] ?? 15);
    }

    public function showTemplate(int $id)
    {
        return PriceTagTemplate::query()->find($id);
    }

    public function saveTemplate(array $data)
    {
        $template = new PriceTagTemplate();
        if (isset($data['id'])) $template = PriceTagTemplate::query()->find($data['id']);

        $template->type = $data['type'];
        $template->name = $data['name'] ?? $template->name;
        $template->data = $data['data'];
        $template->save();

        return $template;
    }

    public function deleteTemplate(int $id): void
    {
        PriceTagTemplate::query()->where('id', $id)->delete();
    }

    public static function checkSennikActive(): void
    {
        $senniks = Sennik::query()->where('status', 1)->where('end_date', '<', now()->subDay())->get();
        Sennik::query()
            ->where('status', 1)
            ->where('end_date', '<', now()->subDay())
            ->update(['status' => 0]);

        foreach ($senniks as $sennik) {
            dispatch(new MoveSennikJob($sennik->id));
        }
    }

    public static function moveSennik(int $sennik_id): void
    {
        $sennik = Sennik::query()->find($sennik_id);
        $sennik_temp = SennikTemp::query()->create($sennik->toArray());

        $sennik->goods()->each(function ($good) use ($sennik_temp) {
            $good_temp = $sennik_temp->goods()->create($good->toArray());
            $good->months()->each(function ($month) use ($good_temp) {
                $good_temp->months()->create($month->toArray());
            });
        });
    }
}
