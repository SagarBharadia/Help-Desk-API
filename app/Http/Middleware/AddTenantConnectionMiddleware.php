<?php

namespace App\Http\Middleware;

use App\GlobalCompanyDatabase;
use Closure;

class AddTenantConnectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Getting the company_subdirectory from the route
        $companySubDirectory = $request->route('company_subdirectory');

        // Attempting to find a record in the global company database
        $databaseRecord = GlobalCompanyDatabase::where('company_url_subdirectory', $companySubDirectory)
            ->first();

        if(empty($databaseRecord)) return response()->json(['message' => 'Not found.'], 404);

        // If there is then add it to the connections list
        addConnectionByName($databaseRecord->company_database_name);
        config(['auth.defaults.guard' => 'tenant_api']);

        return $next($request);
    }
}
