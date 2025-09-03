<?php

namespace App\Services\User;

use App\Http\Integrations\Invoice\InvoiceConnector;
use App\Http\Integrations\Invoice\Requests\GetUserByPinflRequest;
use App\Models\Product\ProductCategory;
use App\Models\User;

class UserService
{
    public function list(array $params)
    {
        $order_by = $request['order_by'] ?? 'id';
        $order_direction = $request['order_direction'] ?? 'desc';

        return User::with(['role', 'branches'])
            ->leftJoin('personal_access_tokens', function ($join) {
                $join->on('personal_access_tokens.tokenable_id', '=', 'users.id')
                    ->whereRaw('personal_access_tokens.id = (SELECT MAX(id) FROM personal_access_tokens WHERE personal_access_tokens.tokenable_id = users.id)');
            })
            ->when(auth()->user()->role_id !== 1, function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->whereIn('branch_id', auth()->user()->branches()->pluck('id'));
                });
            })
            ->when(auth()->user()->role_id === 5, function ($query) {
                $query->where('users.role_id', '=', 2);
            })
            ->when(isset($params['status']), function ($query) use ($params) {
                $query->where('status', '=', $params['status']);
            })
            ->when(isset($params['search']), function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->where('users.name', 'like', "%{$params['search']}%")
                        ->orWhere('users.surname', 'like', "%{$params['search']}%")
                        ->orWhere('users.patronymic', 'like', "%{$params['search']}%")
                        ->orWhere('users.pinfl', 'like', "%{$params['search']}")
                        ->orWhere('users.phone', 'like', "%{$params['search']}");
                });
            })
            ->when(isset($params['role_id']), function ($query) use ($params) {
                $query->where('role_id', $params['role_id']);
            })
            ->when(isset($params['region_id']), function ($query) use ($params) {
                $query->whereRelation('branches', 'region_id', '=', $params['region_id']);
            })
            ->select([
                'users.*',
                'personal_access_tokens.created_at as last_login'
            ])
            ->orderBy($order_by, $order_direction)
            ->paginate($params['per_page'] ?? 10);
    }

    public function add(array $params): void
    {
        $this::checkPhoneUnique($params['phone']);
        $user = User::query()->create($params);

        if (!empty($params['categories'])) {
            UserCategoriesService::create($user->id, $params['categories']);
        }

        UserBranchService::create($user->id, $params['branches']);
    }

    public static function updateRoleId(int $role_id, array $user_ids): void
    {
        User::query()->whereIn('id', $user_ids)->update(['role_id' => $role_id]);
    }

    public function getById(int $id, array $with = []): ?User
    {
        return User::with($with)->find($id);
    }

    public static function checkPhoneUnique(string $phone): void
    {
        if (!is_null(User::query()->where('phone', $phone)->first())) {
            throwError('Phone exists');
        }
    }

    public function changePassword(array $params): void
    {
        $user = auth()->user();
        $user->password = $params['password'];
        $user->save();
    }

    public static function changePhone(string $phone): void
    {
        $user = auth()->user();

        if ($user->phone !== $phone) {
            self::checkPhoneUnique($phone);

            $user->phone = $phone;
            $user->save();
        }
    }

    public function update(int $id, array $params): void
    {
        $user = $this->getById($id);

        if ($params['phone'] !== $user->phone) {
            $this::checkPhoneUnique($params['phone']);
        }

        $user->update($params);

        if (isset($params['categories'])) {
            UserCategoriesService::create($id, $params['categories']);
        }

        UserBranchService::create($id, $params['branches']);
    }

    public function toggleStatus(int $id, bool $status): void
    {
        $user = $this->getById($id);
        $user->status = $status;
        $user->save();
    }

    public function categories(array $params)
    {
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $categories = ProductCategory::query();

        if (auth()->user()->role->category_must_be_added) {
            $categories = auth()->user()->categories();
        }

        $categories->orderBy($order_by, $order_direction);

        if (isset($params['search'])) {
            $categories
                ->where('sku', 'like', "%{$params['search']}%")
                ->orWhere('title', 'like', "%{$params['search']}%");
        }

        return $categories->paginate($params['per_page'] ?? 10);
    }

    public function branches(array $params)
    {
        $order_by = $params['order_by'] ?? 'id';
        $order_direction = $params['order_direction'] ?? 'desc';

        $branches = auth()->user()->branches()->orderBy($order_by, $order_direction);

        if (isset($params['search'])) {
            $branches
                ->where('name', 'like', "%{$params['search']}%")
                ->orWhere('address', 'like', "%{$params['search']}%");
        }

        return $branches->paginate($params['per_page'] ?? 10);
    }

    public function delete(int $id): void
    {
        $user = $this->getById($id);
        $user->status = 0;
        $user->save();
    }

    public function getInfoByPinfl(int $pinfl)
    {
        $check = checkPinflNumber($pinfl);

        if ($check === false){
            throwError('pinfl raqami noto`g`ri');
        }

        $connector = new InvoiceConnector();
        $response = $connector->send(new GetUserByPinflRequest($pinfl));

        $data = json_decode($response->body(),true);

        if (!isset($data['data']) || !isset($data['data']['ns10Code'])) {
            throwError("Bu pinfl raqam bo`yicha ma`lumot topa olmadik: $pinfl");
        }

        $result = $data['data'];
        if (isset($result['fullName'])) {
            $fullName = explode(' ', $result['fullName'], 3);
            $result['name'] = $fullName[1];
            $result['surname'] = $fullName[0];
            $result['patronymic'] = $fullName[2];
        }

        return $data;
    }
}
