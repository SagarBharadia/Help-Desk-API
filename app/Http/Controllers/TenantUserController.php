<?php

namespace App\Http\Controllers;

use App\Rules\StrongPassword;
use App\TenantLogAction;
use App\TenantRole;
use App\TenantUser;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TenantUserController extends Controller
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
   * Create a new tenant user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    // TODO: Do not allow users to be created with the Master role.
    // validate the request
    $this->validate($request, [
      'role_id' => 'required|integer',
      'first_name' => 'required|string',
      'second_name' => 'required|string',
      'email_address' => 'required|email|unique:tenant.users',
      'password' => ['string', 'required', 'confirmed', new StrongPassword]
    ]);

    $user = new TenantUser();
    $user->role_id = $request->get('role_id');
    $user->first_name = $request->get('first_name');
    $user->second_name = $request->get('second_name');
    $user->email_address = $request->get('email_address');
    $user->password = Hash::make($request->get('password'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-user');
    $userActionLog->details = "Created account for ".$user->first_name." ".$user->second_name." (".$user->email_address.").";

    if ($user->save()) {
      if($userActionLog->log_action_id) $userActionLog->save();
      $response = response()->json(['message' => 'User created.'], 201);
    } else {
      $response = response()->json(['message' => 'User failed to save.'], 500);
    }

    return $response;
  }

  /**
   * Update a tenant user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request)
  {
    // TODO: Do not allow users to be updated with the Master role.
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer',
      'role_id' => 'integer',
      'first_name' => 'string',
      'second_name' => 'string',
      'email_address' => 'email|exists:tenant.users',
      'password' => ['string', 'confirmed', new StrongPassword]
    ]);

    $user = TenantUser::find($request->get('user_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-user');

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $userActionLog->details = "Updating details for ".$user->first_name." ".$user->second_name.". Changed:";
      if(!empty($request->get('role_id'))) {
        $newRole = TenantRole::find($request->get('role_id'));
        $userActionLog->details .= " Role[".$user->role->display_name."(".$user->role->name.") -> ".$newRole->display_name."(".$newRole->name.")]";
      }
      if(!empty($request->get('first_name'))) {
        $userActionLog->details .= " First Name[".$user->first_name." -> ".$request->get('first_name')."]";
        $user->first_name = $request->get('first_name');
      }
      if(!empty($request->get('second_name'))) {
        $userActionLog->details .= " Second Name[".$user->second_name." -> ".$request->get('second_name')."]";
        $user->second_name = $request->get('second_name');
      }
      if(!empty($request->get('email_address'))) {
        $userActionLog->details .= " Email Address[".$user->email_address." -> ".$request->get('email_address')."]";
        $user->email_address = $request->get('email_address');
      }
      if(!empty($request->get('password'))) {
        $user->password = Hash::make($request->get('password'));
        $userActionLog->details .= " Password[Password updated]";
      }

      if($user->save()) {
        if($userActionLog->log_action_id) $userActionLog->save();

        $response = response()->json(['message' => 'User updated.'], 204);
      } else {
        $response = response()->json(['message' => 'User updates could not be saved.'], 500);
      }
    }

    return $response;
  }

  /**
   * Toggle the active state of a user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function toggleActive(Request $request)
  {
    // TODO: Do not allow users to be deactivated with the Master role.
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer'
    ]);

    $user = TenantUser::find($request->get('user_id'));
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('toggledActive-for-user');

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $user->active = !$user->active;

      if($user->save()) {
        if($userActionLog->log_action_id) $userActionLog->save();

        $response = response()->json(['message' => 'User updated.'], 204);
      } else {
        $response = response()->json(['message' => 'Could not toggle active state of user.'], 500);
      }
    }

    return $response;
  }

  /**
   * Retrieve users in pagination, including links to get the next page.
   *
   * @return \Illuminate\Contracts\Pagination\Paginator
   */
  public function getAll()
  {
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-user');
    $userActionLog->details = "Accessed all users via /get/all";
    if($userActionLog->log_action_id) $userActionLog->save();
    return DB::connection('tenant')->table('users')->select(['id', 'first_name', 'second_name', 'email_address', 'active'])->simplePaginate();
  }

  /**
   * Get user based on user id.
   *
   * @param int $user_id
   * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\MessageBag
   */
  public function get($user_id)
  {
    // Validating request
    $validator = Validator::make(['user_id' => $user_id], [
      'user_id' => 'required|integer'
    ]);

    if($validator->fails()) return $validator->errors();

    $user = TenantUser::find($user_id);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-user');

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $userActionLog->details = "Accessed user ".$user->first_name." ".$user->second_name." [id: ".$user->id."]";
      if($userActionLog->log_action_id) $userActionLog->save();
      $response = response()->json(['message' => 'User found.', 'user' => $user], 200);
    }

    return $response;
  }

  /**
   * Function that just returns 200 if valid. At this stage the auth guard would've already checked if the user is authenticated.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function checkToken()
  {
    return response()->json([], 200);
  }
}
