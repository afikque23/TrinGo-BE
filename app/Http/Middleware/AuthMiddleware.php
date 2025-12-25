<?php

namespace App\Http\Middleware;

use Closure;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        // check JWT token or session
        return $next($request);
    }
}
