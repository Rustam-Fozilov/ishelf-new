<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\AddRequest;
use App\Http\Requests\Application\ChangeStepRequest;
use App\Http\Requests\Application\ListRequest;
use App\Http\Resources\Resource;
use App\Services\Application\ApplicationService;
use App\Services\RolePerm\PermissionService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $service,
        protected PermissionService $permissionService,
    )
    {
    }

    public function list(ListRequest $request): Resource
    {
        $this->permissionService->hasPermission('applications.list');
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function getById(int $id)
    {
        $this->permissionService->hasPermission('applications.list');
        $data = $this->service->show($id);
        return success($data);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->hasPermission('applications.add');
        $this->service->add($request->validated());
        return success();
    }

    public function changeStep(ChangeStepRequest $request)
    {
        $this->permissionService->hasPermission('applications.change_step');
        $this->service->changeStep($request->validated());
        return success();
    }

    public function delete(int $id)
    {
        $this->permissionService->hasPermission('applications.delete');
        $this->service->delete($id);
        return success();
    }
}
