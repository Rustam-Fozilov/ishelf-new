<?php

namespace App\Http\Controllers\PrintLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrintLog\CreateRequest;
use App\Http\Requests\PrintLog\ListByShelfRequest;
use App\Http\Requests\PrintLog\TopListRequest;
use App\Http\Resources\Resource;
use App\Services\PrintLog\PrintLogService;
use Illuminate\Http\Request;

class PrintLogController extends Controller
{
    public function __construct(
        protected PrintLogService $service
    )
    {
    }

    public function listByShelf(int $shelf_id, ListByShelfRequest $request)
    {
        $data = $this->service->listByShelf($shelf_id, $request->validated());
        return new Resource($data);
    }

    public function create(CreateRequest $request)
    {
        $this->service::create($request['shelf_id'], $request['status']);
        return success();
    }

    public function top(TopListRequest $request)
    {
        $data = $this->service->top($request->validated());
        return new Resource($data);
    }
}
