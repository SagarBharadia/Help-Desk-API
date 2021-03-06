<?php

namespace App\Http\Controllers;

use App\Rules\StrongPassword;
use App\TenantLogAction;
use App\TenantPermission;
use App\TenantPermissionAction;
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
    // validate the request
    $this->validate($request, [
      'role_id' => 'required|integer',
      'first_name' => 'required|string',
      'second_name' => 'required|string',
      'email_address' => 'required|email|unique:tenant.users',
      'password' => ['string', 'required', 'confirmed', new StrongPassword]
    ]);

    $masterRole = TenantRole::getByName('master');
    if ($request->get('role_id') == $masterRole->id) {
      return response()->json(['role_id' => 'Cannot assign master role.'], 422);
    }

    $user = new TenantUser();
    $user->role_id = $request->get('role_id');
    $user->first_name = $request->get('first_name');
    $user->second_name = $request->get('second_name');
    $user->email_address = $request->get('email_address');
    $user->password = Hash::make($request->get('password'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-user');
    $userActionLog->details = "Created account for " . $user->first_name . " " . $user->second_name . " (" . $user->email_address . ").";

    if ($user->save()) {
      if ($userActionLog->log_action_id) $userActionLog->save();
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
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer',
      'role_id' => 'integer',
      'first_name' => 'string',
      'second_name' => 'string',
      'email_address' => 'email|exists:tenant.users',
      'password' => ['string', 'confirmed', new StrongPassword]
    ]);

    $masterRole = TenantRole::getByName('master');

    $user = TenantUser::find($request->get('user_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-user');

    if (empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $returnMsg = "User updated.";
      $userActionLog->details = "Updating details for " . $user->first_name . " " . $user->second_name . ". Changed:";
      if (!empty($request->get('role_id')) && $request->get('role_id') != $masterRole->id && !$user->role->isRole('master')) {
        $newRole = TenantRole::find($request->get('role_id'));
        if ($newRole) {
          $user->role_id = $newRole->id;
          $userActionLog->details .= " Role[" . $user->role->display_name . "(" . $user->role->name . ") -> " . $newRole->display_name . "(" . $newRole->name . ")]";
        }
      } else {
        $returnMsg .= " Except role, you can't change role to or from Master.";
      }
      if (!empty($request->get('first_name'))) {
        $userActionLog->details .= " First Name[" . $user->first_name . " -> " . $request->get('first_name') . "]";
        $user->first_name = $request->get('first_name');
      }
      if (!empty($request->get('second_name'))) {
        $userActionLog->details .= " Second Name[" . $user->second_name . " -> " . $request->get('second_name') . "]";
        $user->second_name = $request->get('second_name');
      }
      if (!empty($request->get('email_address'))) {
        $userActionLog->details .= " Email Address[" . $user->email_address . " -> " . $request->get('email_address') . "]";
        $user->email_address = $request->get('email_address');
      }
      if (!empty($request->get('password'))) {
        $user->password = Hash::make($request->get('password'));
        $userActionLog->details .= " Password[Password updated]";
      }

      if ($user->save()) {
        if ($userActionLog->log_action_id) $userActionLog->save();

        $response = response()->json(['message' => $returnMsg], 200);
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
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer'
    ]);

    $user = TenantUser::find($request->get('user_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('toggledActive-for-user');

    if (empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {

      if ($user->role->isRole('master')) {
        return response()->json(['message' => 'Cannot deactivate account with master role.'], 422);
      }

      $user->active = !$user->active;

      if ($user->save()) {
        if ($userActionLog->log_action_id) $userActionLog->save();

        $response = response()->json(['message' => 'User updated.'], 200);
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
  public function getAll(Request $request)
  {
    $this->validate($request, [
      'filterPerm' => 'string',
      'forForm' => 'string'
    ]);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-user');
    $userActionLog->details = "Accessed all users via /get/all";

    $forForm = false;
    if ($request->get("forForm") && $request->get('forForm') === "true") {
      $forForm = true;
    }

    $response = null;
    $query = TenantUser::select(['id', 'first_name', 'second_name', 'role_id', 'email_address', 'active']);

    $filterPerm = $request->get('filterPerm');
    if ($filterPerm) {
      $permissionAction = TenantPermissionAction::getByAction($filterPerm);
      $permissions = TenantPermission::where('permission_action_id', $permissionAction->id)->get();
      $roleIDs = [];
      foreach ($permissions as $perm) {
        array_push($roleIDs, $perm->role->id);
      }
      $query = $query->whereIn('role_id', $roleIDs);
    }

    if ($forForm) {
      $response = $query->where("active", "=", "1")->get();
    } else {
      $response = $query->simplePaginate();
    }

    if ($userActionLog->log_action_id) $userActionLog->save();

    return $response;
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

    if ($validator->fails()) return $validator->errors();

    $user = TenantUser::find($user_id);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-user');

    if (empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $userActionLog->details = "Accessed user " . $user->first_name . " " . $user->second_name . " [id: " . $user->id . "]";
      if ($userActionLog->log_action_id) $userActionLog->save();
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

  /**
   * Get the user history paginated.
   *
   * @param TenantUser $user
   * @return mixed
   */
  public function userLogs($user_id)
  {
    $user = TenantUser::find($user_id);
    if (!$user) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $userHistory = TenantUserActionLog::with('logAction')
        ->where('user_id', '=', $user_id)
        ->orderBy('created_at', 'desc')
        ->simplePaginate();
      $response = $userHistory;
    }
    return $response;
  }
}
