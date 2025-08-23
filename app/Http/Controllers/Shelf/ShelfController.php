<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Services\RolePerm\PermissionService;
use App\Services\Shelf\ShelfService;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function __construct(
        protected ShelfService $service,
        protected PermissionService $permissionService,
    )
    {
    }
}
