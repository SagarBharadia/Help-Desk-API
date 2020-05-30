<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
  protected function respondWithToken($token, $tenantRecord, $user)
  {
    return response()->json([
      'token' => $token,
      'token_type' => 'bearer',
      'expires_in' => Auth::factory()->getTTL(),
      'company_subdir' => $tenantRecord->company_url_subdirectory,
      'company_name' => $tenantRecord->company_name,
      'user_name' => $user->first_name . " " . $user->second_name
    ], 200);
  }
}
