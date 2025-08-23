<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $service,
    )
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = AuthService::login($request->all());
        return success($data);
    }

    public function me()
    {
        return auth()->user()->load('branches');
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();
        return success();
    }
}
