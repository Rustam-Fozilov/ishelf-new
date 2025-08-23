<?php

namespace App\Services\User;

use App\Models\UserBranch;

class UserBranchService
{
    public static function create(int $user_id, array $branchIds): void
    {
        UserBranch::query()->where('user_id', $user_id)->delete();

        foreach ($branchIds as $id) {
            UserBranch::query()->create([
                'user_id'   => $user_id,
                'branch_id' => $id
            ]);
        }
    }
}
