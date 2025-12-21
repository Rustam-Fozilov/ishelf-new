<?php

namespace App\Http\Controllers\Shelf;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Shelf\ShelfStockPriorityService;
use App\Http\Requests\ShelfStockPriority\AddShelfStockPriorityRequest;

class ShelfStockPriorityController extends Controller
{
    public function __construct(
        protected ShelfStockPriorityService $service,
    )
    {
    }

    public function get(int $shelf_id)
    {
        $data = $this->service->list($shelf_id);
        return success($data);
    }

    public function add(AddShelfStockPriorityRequest $request, int $shelf_id)
    {
        $this->service->add($request->validated(), $shelf_id);
        return success();
    }

    public function delete(int $shelf_id)
    {
        $this->service->delete($shelf_id);
        return success();
    }
}
