<?php

namespace App\Http\Controllers;

use App\Services\Admin\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $service
    )
    {
    }
}
