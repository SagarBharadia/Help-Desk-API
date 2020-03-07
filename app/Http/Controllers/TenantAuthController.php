<?php

namespace App\Http\Controllers;

use App\TenantLogAction;
use App\TenantUser;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TenantAuthController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Attempt to find a user with the credentials provided.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function login(Request $request)
  {

    $userActionLog = new TenantUserActionLog();

    // validate the request
    $this->validate($request, [
      'email_address' => 'required|string',
      'password' => 'required|string'
    ]);

    $credentials = $request->only(['email_address', 'password']);

    // Attempting to login
    if (!$token = Auth::guard('tenant_api')->attempt($credentials)) {
      // Getting the user that was attempted
      $attemptedUser = TenantUser::where('email_address', $request->get('email_address'))->first();
      if(!empty($attemptedUser)) {
        $userActionLog->user_id = $attemptedUser->id;
        $userActionLog->log_action_id = TenantLogAction::getIdOfAction('user-attempted-to-login');
      } else {
        $userActionLog = null;
      }
      $response = response()->json(['message' => 'Unauthorized.'], 401);
    } else {
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('user-logged-in');
      $response = $this->respondWithToken($token, $request->route('company_subdirectory'));
    }

    if(!is_null($userActionLog)) $userActionLog->save();

    // Else return with token
    return $response;

  }

}
