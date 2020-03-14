<?php

namespace App\Http\Middleware;

use App\GlobalCompanyDatabase;
use Closure;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;


class AddTenantSecretMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    // Getting the company_subdirectory from the route
    $companySubDirectory = $request->route('company_subdirectory');

    // Attempting to find a record in the global company database
    $databaseRecord = GlobalCompanyDatabase::where('company_url_subdirectory', $companySubDirectory)
      ->first();

    if (empty($databaseRecord)) return response()->json(['message' => 'Not found.'], 404);

    $secretRoute = $databaseRecord->company_database_name . "/secret.txt";

    $exists = Storage::exists($secretRoute);

    if (!$exists) return response()->json(['message' => 'Secret not found. Please call us immediately.'], 404);

    $secret = Storage::get($secretRoute);

    JWTAuth::getJWTProvider()->setSecret($secret);

    return $next($request);
  }
}
