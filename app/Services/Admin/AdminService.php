<?php

namespace App\Services\Admin;

use App\Http\Integrations\Anketa\AnketaConnector;
use App\Http\Integrations\Anketa\Requests\BranchSyncRequest;
use App\Models\Branch;

class AdminService
{
    public function branchSync(): void
    {
        $request = (new AnketaConnector())->send(new BranchSyncRequest());

        $res = json_decode($request->body(), true);

        if (!isset($res['data'])) throwError('ishlamadi tekshirish kerak');

        foreach ($res['data'] as $item) {
            Branch::query()->updateOrCreate(
                [
                    'token' => $item['token']
                ],
                [
                    'status'    => $item['status'],
                    'name'      => $item['name'],
                    'address'   => $item['address'],
                    'location'  => $item['location'],
                    'region_id' => $item['region_id'],
                    'phones'    => $item['phones'],
                    'link'      => $item['link'],
                    'info'      => $item['info']
                ]

            );
        }
    }
}
