<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\ChangeStatusRequest;
use App\Http\Requests\Branch\ListRequest;
use App\Http\Resources\Resource;
use App\Services\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $service,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        $this->service->changeStatus($request->validated());
        return success();
    }
}
