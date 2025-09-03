<?php

namespace App\Services;

use App\Models\Branch;

class BranchService
{
    public function list(array $params)
    {
        $search = $params['search'] ?? null;
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';
        $search = translit($search);

        return Branch::query()
            ->when(auth()->id() !== 1, function ($query) {
                $query->whereRelation('users', 'user_id', '=', auth()->id());
            })
            ->when(isset($params['status']), function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name','like',"%$search[lat]%")
                        ->orWhere('name','like',"%$search[cyr]%");
                });
            })
            ->when(isset($params['id']), function ($query) use ($params) {
                $query->where('id', $params['id']);
            })
            ->when(isset($params['region_id']), function ($query) use ($params) {
                $query->where('region_id', $params['region_id']);
            })
            ->when(isset($params['source']), function ($query) {
                $query->select(['id', 'name', 'status']);
            }, function ($query) {
                $query->with('region')->withCount('shelf');
            })
            ->orderBy($order_by,$order_direction)
            ->paginate($params['per_page'] ?? 15);
    }

    public function changeStatus($data): void
    {
        $branch = Branch::query()->find($data['branch_id']);
        $branch->update(['status' => $data['status']]);
    }
}
