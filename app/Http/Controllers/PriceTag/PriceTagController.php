<?php

namespace App\Http\Controllers\PriceTag;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceTag\AttachBranchRequest;
use App\Http\Requests\PriceTag\AttachTemplateRequest;
use App\Http\Requests\PriceTag\ChangeStepRequest;
use App\Http\Requests\PriceTag\ListRequest;
use App\Http\Requests\PriceTag\PrintRequest;
use App\Http\Resources\Resource;
use App\Services\PriceTag\PriceTagService;
use App\Services\RolePerm\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceTagController extends Controller
{
    public function __construct(
        protected PriceTagService $service,
        protected PermissionService $permissionService,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->hasPermission('priceTag.list');
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function print(PrintRequest $request)
    {
        $this->service->print($request->validated());
        return success();
    }

    public function analyticList(Request $request)
    {
        $data =$this->service->analyticList($request->all());
        return success($data);
    }

    public function analyticByBranchSennik(Request $request, int $branch_id, int $sennik_id)
    {
        $data =$this->service->analyticByBranchSennik($request->all(), $branch_id, $sennik_id);
        return success($data);
    }

    public function sennikList(Request $request)
    {
        $this->permissionService->hasPermission('priceTag.list');
        $data = $this->service->sennikList($request->all());
        return new Resource($data);
    }

    public function sennikTempList(Request $request): Resource
    {
        $this->permissionService->hasPermission('priceTag.temp_list');
        $data = $this->service->sennikTempList($request->all());
        return new Resource($data);
    }

    public function sennikSelect(Request $request): Resource
    {
        $this->permissionService->hasPermission('priceTag.list');
        $data = $this->service->sennikSelect($request->all());
        return new Resource($data);
    }

    public function sennikShow(int $id, Request $request): JsonResponse
    {
        $this->permissionService->hasPermission('priceTag.list');
        $data = $this->service->sennikShow($id, $request->all());
        return success($data);
    }

    public function sennikShowTemp(int $id, Request $request): JsonResponse
    {
        $this->permissionService->hasPermission('priceTag.temp_list');
        $data = $this->service->sennikShowTemp($id, $request->all());
        return success($data);
    }

    public function sennikAttachTemplate(AttachTemplateRequest $request): JsonResponse
    {
        $this->permissionService->hasPermission('priceTag.bind');
        $this->service->sennikAttachTemplate($request->validated());
        return success();
    }

    public function sennikCheckAmount(int $id): JsonResponse
    {
        $data = $this->service->sennikCheckAmount($id);
        return success($data);
    }

    public function sennikChangeStep(ChangeStepRequest $request): JsonResponse
    {
        $this->service->sennikChangeStep($request->all());
        return success();
    }

    public function sennikDelete(int $id): JsonResponse
    {
        $this->permissionService->hasPermission('priceTag.delete');
        $this->service->sennikDelete($id);
        return success();
    }

    public function attachBranch(AttachBranchRequest $request): JsonResponse
    {
        $this->service->attachBranch($request->validated());
        return success();
    }
}
