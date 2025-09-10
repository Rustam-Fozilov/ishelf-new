<?php

namespace App\Services\User;

use App\Models\User\UserBranch;
use Illuminate\Support\Collection;

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

    public static function getDirectorsByBranch(int $branch_id): Collection
    {
        return UserBranch::with(['user'])
            ->where('branch_id', $branch_id)
            ->whereRelation('user', 'role_id', '=', 2)
            ->whereRelation('user', 'status', '=', 1)
            ->distinct('user_id')
            ->get()->pluck('user');
    }

    public static function getRegionalDirectorsByBranch(int $branch_id): Collection
    {
        return UserBranch::with(['user'])
            ->where('branch_id', $branch_id)
            ->whereRelation('user.role', 'title', '=', 'RSM')
            ->whereRelation('user', 'status', '=', 1)
            ->distinct('user_id')
            ->get()->pluck('user');
    }
}
