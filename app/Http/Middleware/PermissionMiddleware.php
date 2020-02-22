<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $permission
     * @return mixed
     */
    public function handle($request, Closure $next, string $permission)
    {
        if(Auth::user() && Auth::user()->isAllowedTo($permission))
        {
            return $next($request);
        }
        return response()->json(['message' => 'Unauthorized.'], 401);
    }
}
