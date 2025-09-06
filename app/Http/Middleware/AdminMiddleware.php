<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (is_null(auth('sanctum')->user()) || auth('sanctum')->user()->is_admin == 0) {
            return response()->json(['message' => 'Permission denied'], 403);
        }
        return $next($request);
    }
}
