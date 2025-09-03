<?php

namespace App\Services\RolePerm;

use App\Models\RolePerm\Permission;
use App\Models\RolePerm\RolePerms;
use App\Services\RolePerm\RolePermsService;

class PermissionService
{
    public static function create(array $params): void
    {
        self::checkKeyUnique($params['key']);

        if ($params['is_parent'] == 0) {
            self::checkByParent($params['parent_id']);
        }

        $perm = Permission::query()->create([
            'key'       => $params['key'],
            'parent_id' => $params['parent_id'],
            'is_parent' => $params['is_parent'],
            'title'     => $params['title'],
            'type'      => $params['type'],
            'options'   => $params['options'] ?? null
        ]);

        self::addRolePermsWithPermission($perm);
    }

    public static function checkById(int $id): void
    {
        $perm = Permission::query()->find($id);
        if (is_null($perm)) {
            throwError('Permission not found');
        }
    }

    public static function getById($id)
    {
        return Permission::query()->find($id);
    }

    public static function checkKeyUnique(string $key): void
    {
        $perm = Permission::query()->where('key', $key)->first();
        if (!is_null($perm)) {
            throwError('Key already exists');
        }
    }

    public static function checkByParent(int $id): void
    {
        self::checkById($id);

        $parent = self::getById($id);

        if ($parent->is_parent == 0) {
            throwError('Parent permission is not parent');
        }
    }

    public static function addRolePermsWithPermission(Permission $permission): void
    {
        $roles = (new RoleService())->list();

        foreach ($roles as $role) {
            RolePerms::query()->create([
                'key'           => $permission->key,
                'value'         => 0,
                'role_id'       => $role->id,
                'permission_id' => $permission->id,
            ]);
        }
    }

    public static function update(int $id, array $params): void
    {
        self::checkById($id);

        if ($params['is_parent'] == 0) {
            self::checkByParent($params['parent_id']);
        }

        self::checkUniqueWithoutId($id, $params['key']);

        $perm = self::getById($id);

        $old_key = $perm->key;
        $old_type = $perm->type;
        $old_options = is_array($perm->options) ? implode('_', $perm->options) : '';

        $perm->update([
            'key'       => $params['key'],
            'parent_id' => $params['parent_id'],
            'is_parent' => $params['is_parent'],
            'title'     => $params['title'],
            'type'      => $params['type'],
            'options'   => $params['options'] ?? null
        ]);

        self::updateRolePerms($perm, $old_key, $old_type, $old_options);
    }

    public static function checkUniqueWithoutId(int $id, string $key): void
    {
        $perm = Permission::query()->where('key', $key)->where('id', '!=', $id)->first();
        if (!is_null($perm)) {
            throwError('Key is already exists');
        }
    }

    public static function updateRolePerms(Permission $permission, string $old_key, string $old_type, string $old_options): void
    {
        $updateData = [];

        if ($permission->key !== $old_key) {
            $updateData['key'] = $permission->key;
        }

        if ($permission->type !== $old_type) {
            if ($permission->type === 'list') {
                $updateData['value'] = $permission->options[0];
            } else {
                $updateData['value'] = 0;
            }
        } elseif ($permission->type === 'list') {
            if (implode('_', $permission->options) !== $old_options) {
                $updateData['value'] = $permission->options[0];
            }
        }

        if (!empty($updateData)) {
            RolePerms::query()->where('permission_id', $permission->id)->update($updateData);
        }
    }

    public static function list()
    {
        return Permission::query()->get();
    }

    public static function delete(int $id): void
    {
        self::checkById($id);

        $permission = self::getById($id);

        foreach ($permission->children as $child) {
            RolePerms::query()->where('permission_id', $child->id)->delete();
            Permission::query()->where('id', $child->id)->delete();
        }

        RolePerms::query()->where('permission_id', $permission->id)->delete();
        $permission->delete();
    }

    public static function checkValue($value, $type): bool
    {
        if ($type == 'flag' && in_array($value, ['0', '1'])) {
            return true;
        } elseif ($type == 'list' && in_array($value, ['0', 'own', 'all'])) {
            return true;
        } elseif ($type == 'numeric' && is_numeric($value) && strlen($value) <= 20) {
            return true;
        }

        return false;
    }

    public function isAllow($key, $value, $redirect = false): bool
    {
        $user = auth()->user();

        if ($user->is_admin) return true;

        $allow = RolePermsService::getAllow($user->role_id, $key);

        if ($allow == $value) return true;

        if ($redirect) $this->forbidden();

        return false;
    }

    public function hasPermission($key, $redirect = true): bool
    {
        $user = auth()->user();
        if ($user->is_admin) return true;

        $allow = RolePermsService::getAllow($user->role_id, $key);
        if ($allow != 0) return true;

        if ($redirect) $this->forbidden($key);
        return false;
    }

    public static function getAllow($key, $redirect = false, $itemUserId = 0, $field = 'id'): string
    {
        $user = auth()->user();

        if ($user->is_admin) return 'admin';

        $allow = RolePermsService::getAllow($user->role_id, $key);

        // agar faqatgina "own" ga ruxsat bo'lsa va yuborilgan ma'lumotning user_id`si user ID`siga teng bo'lmasa
        if ($redirect && (
                !$allow || $itemUserId && (
                    $allow == 'own' && $itemUserId != $user->$field
                )
            )
        ) {
            self::forbidden($key);
        }

        return $allow;
    }

    public static function forbidden($key = false): void
    {
        if ($key) {
            $permission = Permission::query()->where('key', $key)->first();
            if ($permission) {
                $message = __('errors.forbidden_role', ['role' => $permission->title]);
            } else {
                $message = __('errors.role_not_found', ['role' => $key]);
            }
        } else {
            $message = __('errors.forbidden');
        }

        throwError($message, 403);
    }
}
