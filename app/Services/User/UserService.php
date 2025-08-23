<?php

namespace App\Services\User;

use App\Models\User;

class UserService
{
    public static function updateRoleId(int $role_id, array $user_ids): void
    {
        User::query()->whereIn('id', $user_ids)->update(['role_id' => $role_id]);
    }

    public function getById(int $id, array $with = []): ?User
    {
        return User::with($with)->find($id);
    }
}
