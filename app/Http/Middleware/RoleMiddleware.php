<?php

namespace App\Http\Middleware;

use Closure;

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
        if($request->user() && $request->user()->role->isRole($role))
        {
            return $next($request);
        }
        return response()->json(['message' => 'Not allowed.'], 405);
    }
}
