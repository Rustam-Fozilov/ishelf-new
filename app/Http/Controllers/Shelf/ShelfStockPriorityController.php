<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShelfStockPriority\AddShelfStockPriorityRequest;
use App\Services\RolePerm\PermissionService;
use App\Services\Shelf\ShelfStockPriorityService;
use Illuminate\Http\Request;

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
