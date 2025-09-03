<?php

namespace App\Services\RolePerm;

use App\Models\RolePerm\Role;
use App\Models\RolePerm\Permission;
use App\Models\RolePerm\RolePerms;
use App\Services\User\UserService;

class RolePermsService
{
    public function save(array $params): void
    {
        $with_role = $params['update_with'] == 'role';
        if ($params['update_with'] == 'role') {
            $field_A = 'role_id';
            $field_B = 'permission_id';

            foreach ($params['allows'] as $allow) {
                RoleService::checkById($allow['role_id']);
            }
        } else {
            $field_A = 'permission_id';
            $field_B = 'role_id';

            foreach ($params['allows'] as $allow) {
                PermissionService::checkById($allow['permission_id']);
            }
        }

        $rolePerms = RolePerms::query()->where($field_A, $params['id'])->get();
        $values = [];

        foreach ($rolePerms as $role_perms) {
            $values[$role_perms->$field_B] = $role_perms->value;
        }

        if (!$with_role) {
            $permission = Permission::query()->find($params['id']);
        }

        foreach ($params['allows'] as $allow) {
            $value = $allow['value'];

            if (@$values[$allow[$field_B]] != $value) {
                if ($with_role) {
                    $permission = Permission::query()->find($allow[$field_B]);
                }

                $check = PermissionService::checkValue($value, @$permission->type);
                if ($check !== false) {
                    $role_perms = RolePerms::query()->where($field_A, $params['id'])->where($field_B, $allow[$field_B])->first();

                    if (!$role_perms) {
                        $role_perms = new RolePerms();
                        $role_perms->$field_A = $params['id'];
                        $role_perms->$field_B = $allow[$field_B];
                        $role_perms->key = $permission->key;
                        $role_perms->value = $value;
                        $role_perms->save();
                    } else {
                        RolePerms::query()
                            ->where($field_A, $params['id'])
                            ->where($field_B, $allow[$field_B])
                            ->update(['value' => $value]);
                    }
                }
            }
        }
    }

    public function getByPermission(int $id): array
    {
        PermissionService::checkById($id);
        $permission = PermissionService::getById($id);
        $list = [];

        foreach (Role::query()->where('status', 1)->get() as $role) {
            $role_perms = RolePerms::query()->where('role_id', $role->id)
                ->where('permission_id', $permission->id)
                ->first();

            if (!$role_perms) $role_perms = RolePerms::query()->create([
                'role_id'       => $role->id,
                'permission_id' => $permission->id,
                'key'           => $permission->key,
                'value'         => "0",
            ]);

            $role_perms->role = $role;
            $list[] = $role_perms;
        }

        return array('list' => $list, 'permission' => $permission);
    }

    public function getByRole(int $role_id, $withChildren): array
    {
        $role = RoleService::checkById($role_id);

        if ($withChildren == 1) {
            $list = Permission::query()->where('parent_id', 0)->with('children')->get()->toArray();

            foreach ($list as $key => $item) {
                $list[$key]['value'] = RolePerms::query()
                    ->where('permission_id', $item['id'])
                    ->where('role_id', $role->id)
                    ->first()->value ?? "0";

                foreach ($item['children'] as $k => $v) {
                    $list[$key]['children'][$k]['value'] = RolePerms::query()
                        ->where('permission_id', $v['id'])
                        ->where('role_id', $role->id)
                        ->first()->value ?? "0";
                }
            }
        } else {
            $list = [];

            foreach (Permission::all() as $permission) {
                $role_perms = RolePerms::query()
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permission->id)
                    ->first();

                if (!$role_perms) $role_perms = RolePerms::query()->create([
                    'role_id'       => $role->id,
                    'permission_id' => $permission->id,
                    'key'           => $permission->key,
                    'value'         => "0",
                ]);

                $role_perms->permission = $permission;
                $list[] = $role_perms;
            }
        }

        return array('list' => $list, 'role' => $role);
    }

    public function getUserRoles(bool|int $id)
    {
        $user = $id ? (new UserService())->getById($id) : auth()->user();
        return RolePerms::query()->where('role_id', $user->role_id)->get();
    }

    public static function getAllow($role_id, $key)
    {
        $rolePerms = RolePerms::query()->where('role_id', $role_id)->where('key', $key)->first();

        if (!$rolePerms) {
            $permission = Permission::query()->where('key', $key)->first();
            if (!$permission) return false;

            $rolePerms = RolePerms::query()->create([
                'role_id'       => $role_id,
                'permission_id' => $permission->id,
                'key'           => $key,
                'value'         => 0
            ]);
        }

        return $rolePerms->value;
    }

    public static function saveByRole(int $role_id, array $allows): void
    {
        RolePerms::query()->where('role_id', $role_id)->delete();

        $insert = [];
        foreach ($allows as $allow) {
            $permission = Permission::query()->find($allow['permission_id']);

            $check = PermissionService::checkValue($allow['value'], $permission->type);

            if ($check) {
                $insert[] = [
                    'role_id'       => $role_id,
                    'permission_id' => $allow['permission_id'],
                    'key'           => $permission->key,
                    'value'         => $allow['value'],
                ];
            }
        }

        RolePerms::query()->insert($insert);
    }
}
