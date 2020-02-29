<?php

namespace App\Http\Controllers;

use App\TenantLogAction;
use App\TenantRole;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantRoleController extends Controller
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
   * Create a role.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|string',
      'display_name' => 'required|string'
    ]);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-role');
    $userActionLog->details = "Created role " . $request->get('name');

    $role = new TenantRole();
    $role->name = $request->get('name');
    $role->display_name = $request->get('display_name');
    $role->protected_role = 0;

    if ($role->save()) {
      $userActionLog->save();
      $response = response()->json(['message' => 'Created role.'], 204);
    } else {
      $response = response()->json(['message' => 'Couldn\'t create role.'], 500);
    }

    return $response;
  }

  /**
   * Update a role (excluding permissions).
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request)
  {
    $this->validate($request, [
      'role_id' => 'required|integer',
      'name' => 'string',
      'display_name' => 'string'
    ]);

    $role = TenantRole::find($request->get('role_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-role');

    if(!$role) {
      $response = response()->json(['message' => 'Couldn\'t find role.'], 404);
    } else {
      $userActionLog->details = "Updated role ".$role->display_name.".";

      if(!empty($request->get('name'))) {
        $userActionLog->details .= " Name[".$role->name."->".$request->get('name')."]";
        $role->name = $request->get('name');
      }
      if(!empty($request->get('display_name'))) {
        $userActionLog->details .= " Display Name[".$role->display_name."->".$request->get('display_name')."]";
        $role->display_name = $request->get('display_name');
      }

      if($role->save()) {
        if($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Updated role.'], 204);
      } else {
        $response = response()->json(['message' => 'Couldn\'t save changes.'], 500);
      }
    }

    return $response;
  }

  public function delete(Request $request)
  {
    return response()->json([], 501);
  }

  public function getAll()
  {
    return response()->json([], 501);
  }

  public function get($role_id)
  {
    return response()->json([], 501);
  }
}
