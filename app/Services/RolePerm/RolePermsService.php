<?php

namespace App\Services\RolePerm;

use App\Models\RolePerm\Permission;
use App\Models\RolePerm\RolePerms;
use App\Services\RolePerm\PermissionService;

class RolePermsService
{
    public static function getAllow($role_id, $key)
    {
        $rolePerms = RolePerms::query()->where('role_id', $role_id)->where('key', $key)->first();

        if (!$rolePerms) {
            $permission = Permission::query()->where('key', $key)->first();
            if (!$permission) return false;

            $rolePerms = RolePerms::query()->create([
                'role_id' => $role_id,
                'permission_id' => $permission->id,
                'key' => $key,
                'value' => 0
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
