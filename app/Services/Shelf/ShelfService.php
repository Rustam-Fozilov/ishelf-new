<?php

namespace App\Services\Shelf;

use App\Models\Shelf\Shelf;
use Illuminate\Support\Facades\DB;

class ShelfService
{
    public function add(array $params)
    {
        DB::beginTransaction();

        try {
            $checkService = new ShelfCheckService();
            $checkService->checkUnique($params['branch_id'], $params['category_sku']);
            $is_phone = $checkService->isPhone($params['sku']);

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
