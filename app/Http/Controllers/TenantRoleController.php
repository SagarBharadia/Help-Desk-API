<?php

namespace App\Http\Controllers;

use App\TenantLogAction;
use App\TenantPermission;
use App\TenantPermissionAction;
use App\TenantRole;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
      'name' => 'required|string|unique:tenant.roles',
      'display_name' => 'required|string|unique:tenant.roles',
      'appliedPermissions' => 'array',
    ]);

    $appliedPermissions = $request->get('appliedPermissions');
    $permErrors = [];
    if (!empty($appliedPermissions) && is_array($appliedPermissions)) {
      foreach ($appliedPermissions as $appliedPerm) {
        $permAction = TenantPermissionAction::where('action', '=', $appliedPerm)->first();
        if (empty($permAction)) {
          $msg = $appliedPerm . " doesn't exist.";
          array_push($permErrors, $msg);
        }
      }
    }

    if (!empty($permErrors)) {
      $response = response()->json(['appliedPermissions' => $permErrors], 422);
    } else {
      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-role');
      $userActionLog->details = "Created role " . $request->get('name');

      $role = new TenantRole();
      $role->name = $request->get('name');
      $role->display_name = $request->get('display_name');
      $role->protected_role = 0;

      $role->save();

      if ($role->save()) {

        foreach ($appliedPermissions as $applPerm) {
          $permAction = TenantPermissionAction::where('action', '=', $applPerm)->first();
          $perm = new TenantPermission();
          $perm->permission_action_id = $permAction->id;
          $perm->role_id = $role->id;
          $perm->save();
        }

        if ($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Created role.'], 201);
      } else {
        $response = response()->json(['message' => 'Couldn\'t create role.'], 500);
      }
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
      'display_name' => 'string',
      'appliedPermissions' => 'array'
    ]);

    $role = TenantRole::find($request->get('role_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-role');

    if (!$role) {
      $response = response()->json(['message' => 'Couldn\'t find role.'], 404);
    } else {
      $userActionLog->details = "Updated role " . $role->display_name . ".";

      $appliedPermissions = $request->get('appliedPermissions');

      if (!empty($request->get('name'))) {
        $userActionLog->details .= " Name[" . $role->name . "->" . $request->get('name') . "]";
        $role->name = $request->get('name');
      }
      if (!empty($request->get('display_name'))) {
        $userActionLog->details .= " Display Name[" . $role->display_name . "->" . $request->get('display_name') . "]";
        $role->display_name = $request->get('display_name');
      }

      if ($role->save()) {

        // Deleting permissions
        foreach ($role->permissions as $perm) {
          if (!in_array($perm->permissionAction->action, $appliedPermissions)) {
            $perm->delete();
          }
        }

        $rolePermissionsAsArray = $role->permissions->toArray();

        // Adding permissions
        foreach ($appliedPermissions as $applPerm) {
          // Filter instead of a in_array to see if $applPerm is in $role->permissions
          $ifItExistsAlready = array_filter($rolePermissionsAsArray, function($permToCheck) use ($applPerm) {
            return $permToCheck['permission_action']['action'] == $applPerm;
          });
          if (!$ifItExistsAlready) {
            $newPermAction = TenantPermissionAction::where('action', '=', $applPerm)->first();
            if ($newPermAction) {
              $newPerm = new TenantPermission();
              $newPerm->role_id = $role->id;
              $newPerm->permission_action_id = $newPermAction->id;
              $newPerm->save();
            }
          }
        }

        if ($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Updated role.'], 201);
      } else {
        $response = response()->json(['message' => 'Couldn\'t save changes.'], 500);
      }
    }

//    return $response;
  }

  /**
   * Delete role if no users are assigned it.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function delete(Request $request)
  {
    $this->validate($request, [
      'role_id' => 'required|integer'
    ]);

    $role = TenantRole::find($request->get('role_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('deleted-role');

    if (!$role) {
      $response = response()->json(['message' => 'Role not found.'], 404);
    } else {
      $userActionLog->details = "Deleted role " . $role->display_name . "(" . $role->name . ")";
      if ($role->users->isEmpty()) {
        foreach ($role->permissions as $permission) {
          $permission->delete();
        }
        $role->delete();
        if ($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Delete role.'], 201);
      } else {
        $response = response()->json(['message' => 'Unable to delete role as users are still assigned it.'], 403);
      }
    }

    return $response;
  }

  /**
   * Retrieve all roles with simplePaginate.
   *
   * @return \Illuminate\Contracts\Pagination\Paginator
   */
  public function getAll(Request $request)
  {
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-role');
    $userActionLog->details = "Retrieved all roles using /get/all";

    if ($request->get("forForm") && $request->get('forForm') === "true") {
      $data = TenantRole::all();
      $userActionLog->details .= "?forForm='true'";
    } else {
      $data = DB::connection('tenant')->table('roles')->simplePaginate();
    }

    if ($userActionLog->log_action_id) $userActionLog->save();
    return $data;
  }

  /**
   * Retrieve single role.
   *
   * @param $role_id
   * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\MessageBag
   */
  public function get($role_id)
  {
    // Validating request
    $validator = Validator::make(['role_id' => $role_id], [
      'role_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $role = TenantRole::find($role_id);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-role');

    if (!$role) {
      $response = response()->json(['message' => 'Role not found.'], 404);
    } else {
      $userActionLog->details = "Retrieved role " . $role->name;
      if ($userActionLog->log_action_id) $userActionLog->save();
      $newPermissions = [];
      foreach ($role->permissions as $permission) {
        array_push($newPermissions, $permission->permissionAction->action);
      }
      $role = $role->toArray();
      $role['permissions'] = $newPermissions;
      $response = response()->json(['message' => 'Role found.', 'role' => $role], 200);
    }

    return $response;
  }
}
