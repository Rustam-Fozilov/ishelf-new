<?php

namespace App\Services\Application;

use App\Models\Application\Application;
use App\Services\RolePerm\PermissionService;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    public function list(array $data): LengthAwarePaginator
    {
        $order_by = $data['order_by'] ?? 'id';
        $order_direction = $data['order_direction'] ?? 'desc';

        $list = Application::with(['regions', 'branches', 'category', 'owner', 'upload', 'document', 'history'])
            ->join('users', 'applications.owner_id', '=', 'users.id')
            ->join('product_categories', 'applications.category_sku', '=', 'product_categories.sku')
            ->where('applications.status', '!=', -1)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->where('users.name', 'like', '%' . $data['search'] . '%')
                    ->orWhere('users.surname', 'like', '%' . $data['search'] . '%')
                    ->orWhere('users.patronymic', 'like', '%' . $data['search'] . '%');
            })
            ->when(isset($data['category_sku']), function ($query) use ($data) {
                $query->where('applications.category_sku', $data['category_sku']);
            })
            ->when(isset($data['step']), function ($query) use ($data) {
                $query->where('applications.step', $data['step']);
            })
            ->when(isset($data['status']), function ($query) use ($data) {
                $query->where('applications.status', $data['status']);
            })
            ->when(!empty($data['branch_id']), function ($query) use ($data) {
                $query->whereHas('branches', function ($q) use ($data) {
                    $q->whereIn('id', $data['branch_id']);
                });
            })
            ->when(!empty($data['region_id']), function ($query) use ($data) {
                $query->whereHas('regions', function ($q) use ($data) {
                    $q->whereIn('id', $data['region_id']);
                });
            })
            ->select('applications.*');

        $perm = PermissionService::getAllow('applications.list');
        if ($perm == 'own') $list->where('applications.owner_id', auth()->id());

        if ($order_by == 'app_histories.step2') {
            $apps = $list->get()->sortBy(function ($app) {
                $history = $app->history->where('step', 2)->last();
                $duration = date_diff($history->created_at ?? now(), now())->format('%a.%h');
                return (float) $duration;
            }, descending: $order_direction === 'desc');

            $apps = $this->manualPaginate($apps, $data['page'] ?? 1, $data['per_page'] ?? 15);
        } else if ($order_by == 'app_histories.step3') {
            $apps = $list->get()->sortBy(function ($app) {
                $history = $app->history->where('step', 3)->last();
                $duration = date_diff($history->created_at ?? now(), now())->format('%a.%h');
                return (float) $duration;
            }, descending: $order_direction === 'desc');

            $apps = $this->manualPaginate($apps, $data['page'] ?? 1, $data['per_page'] ?? 15);
        } else {
            $apps = $list->orderBy($order_by, $order_direction)->paginate($data['per_page'] ?? 15);
        }

        return $apps;
    }

    public static function show(int $id)
    {
        return Application::with(['branches', 'regions', 'category', 'owner', 'upload', 'document', 'history.user'])->findOrFail($id);
    }

    public function manualPaginate($data, $current_page, $per_page): LengthAwarePaginator
    {
        $pagedData = $data->slice(($current_page - 1) * $per_page, $per_page)->values();
        return new LengthAwarePaginator($pagedData, $data->count(), $per_page, $current_page);
    }

    public function add(array $params): void
    {
        $app = Application::query()->create([
            'step'         => 1,
            'comment'      => $params['comment'] ?? null,
            'owner_id'     => auth()->id(),
            'upload_id'    => $params['upload_id'] ?? null,
            'document_id'  => $params['document_id'] ?? null,
            'category_sku' => $params['category_sku'],
        ]);

        $app->branches()->sync($params['branch_id'] ?? []);
        $app->regions()->sync($params['region_id'] ?? []);
        $app->history()->create(['step' => 1, 'user_id' => auth()->id(), 'comment' => $params['comment'] ?? null]);
    }

    public function changeStep(array $params): void
    {
        $app = Application::query()->where('id', $params['app_id'])->where('status', 1)->first();
        if (!$app) throwError("Ariza topilmadi!");

        $app->update(['step' => $params['step']]);
        $app->history()->create(['step' => $params['step'], 'user_id' => auth()->id(), 'comment' => $params['comment'] ?? null]);

        if ($params['step'] == 3) $app->update(['status' => 0]);
    }

    public function delete(int $id): void
    {
        $app = Application::query()->where('id', $id)->where('status', 1)->first();
        if (!$app) throwError("Ariza topilmadi!");

        $app->update(['status' => -1]);
        $app->history()->create(['step' => -1, 'user_id' => auth()->id(), 'comment' => "o'chirildi"]);
    }
}
