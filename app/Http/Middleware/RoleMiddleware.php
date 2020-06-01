<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle($request, Closure $next, string $role)
    {
        if(Auth::guard('global_api')->user()->role->isRole($role))
        {
            return $next($request);
        }
        return response()->json(['message' => 'Not allowed.'], 401);
    }
}
