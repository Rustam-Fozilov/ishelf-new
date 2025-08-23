<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectsTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = 'Bearer UsEoP9Bsc6F3fBLAJpvAIUGGB8bhAuGigxQx5U9sOkIDoZOIH5DUycxgtZ4V7rOw';

        if ($request->headers->get('Authorization') !== $token) {
            return response()->json('Forbidden!', 403);
        }

        return $next($request);
    }
}
