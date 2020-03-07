<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
  protected function respondWithToken($token, $company_subdir)
  {
    return response()->json([
      'token' => $token,
      'token_type' => 'bearer',
      'expires_in' => Auth::factory()->getTTL() * 60,
      'company_subdir' => $company_subdir
    ], 200);
  }
}
