<?php

namespace App\Services\RolePerm;

use App\Models\RolePerm\Role;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function list(bool $withUser = false)
    {
        return Role::query()
            ->when($withUser, function ($query) {
                $query->with('users');
            })
            ->get();
    }

    public function show(int $id)
    {
        return Role::with('users')->findOrFail($id);
    }

    public function create(array $params): void
    {
        DB::beginTransaction();

        try {
            $this->checkByName($params['title']);

            $role = Role::query()->create($params);

            if (!empty($params['allows'])) {
                RolePermsService::saveByRole($role->id, $params['allows']);
            }

            if (!empty($params['users'])) {
                UserService::updateRoleId($role->id,  $params['users']);
            }

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }

    public function checkByName(string $title): void
    {
        $role = Role::query()->where('title', $title)->first();

        if (!is_null($role)) {
            throwError(__('role.exists'));
        }
    }

    public function update(array $params): void
    {
        DB::beginTransaction();

        try {
            $role = Role::query()->findOrFail($params['id']);
            $role->update($params);

            if (!empty($params['allows'])) {
                RolePermsService::saveByRole($role->id, $params['allows']);
            }

            if (!empty($params['users'])) {
                UserService::updateRoleId($role->id, $params['users']);
            }

            DB::commit();
        } catch (\Throwable $e) {
            throwResponse($e);
        }
    }
}
