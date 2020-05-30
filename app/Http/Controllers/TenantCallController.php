<?php

namespace App\Http\Controllers;


use App\TenantCall;
use App\TenantCallUpdate;
use App\TenantLogAction;
use App\TenantPermission;
use App\TenantPermissionAction;
use App\TenantRole;
use App\TenantUserActionLog;
use App\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TenantCallController extends Controller
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
   * Create a new call.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    // Validating the request
    $this->validate($request, [
      'client_id' => 'required|integer',
      'caller_name' => 'required|string',
      'name' => 'required|string',
      'details' => 'required|string',
      'tags' => 'required|array'
    ]);

    // Getting roles which have the update-call perm with the least calls assigned to them.
    $permissionAction = TenantPermissionAction::getByAction("update-call");
    $permissions = TenantPermission::where('permission_action_id', $permissionAction->id)->get();
    $roleIDs = [];
    foreach ($permissions as $perm) {
      array_push($roleIDs, $perm->role->id);
    }
    $users = TenantUser::whereIn('role_id', $roleIDs)->get();
    $users = $users->sort(function ($a, $b) {
      $aCount = $a->activeCalls()->count();
      $bCount = $b->activeCalls()->count();
      return $aCount - $bCount;
    })->flatten();

    $call = new TenantCall();
    $call->receiver_id = Auth::guard('tenant_api')->user()->id;
    $call->current_analyst_id = $users[0]->id;
    $call->client_id = $request->get('client_id');
    $call->caller_name = $request->get('caller_name');
    $call->name = $request->get('name');
    $call->details = $request->get('details');
    $trimmedTags = array_map(function ($item) {
      $item = trim($item);
      $item = strtolower($item);
      return $item;
    }, $request->get("tags"));
    $trimmedTags = array_unique($trimmedTags);
    $call->tags = " " . implode(" | ", $trimmedTags) . " ";
    $call->resolved = 0;

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-call');
    $userActionLog->details = "Created call '" . $call->name . "'.";

    if ($call->save()) {
      $response = response()->json(["message" => "Call created"], 200);
      if ($userActionLog->log_action_id) $userActionLog->save();
    } else {
      $response = response()->json(['message' => 'Could not save call.'], 500);
    }

    return $response;
  }

  /**
   * Update a call by creating a new \App\TenantCallUpdate.
   * Also allowing the update of the call itself (tags and resolved only).
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request)
  {
    $this->validate($request, [
      'call_id' => 'required|integer',
      'details' => 'required|string',
      'tags' => 'required|array',
      'resolved' => 'bool',
      'current_analyst_id' => 'required|integer'
    ]);

    $call = TenantCall::find($request->get('call_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-call');

    if (!$call) {
      $response = response()->json(['message' => 'No call found.'], 404);
    } else {
      // Check to see if the user is master or the user is the current analyst for the call.
      if ($call->current_analyst_id != Auth::guard('tenant_api')->user()->id && !Auth::guard('tenant_api')->user()->role->isRole('master')) {
        $response = response()->json(['message' => 'Unable to update call. You have not been assigned this call.'], 403);
      } else {
        $issues = [];
        $callUpdate = new TenantCallUpdate();
        $callUpdate->user_id = Auth::guard('tenant_api')->user()->id;
        $callUpdate->call_id = $call->id;

        $userActionLog->details = "Updated call '" . $call->name . "'. Updated: ";

        if (!empty($request->get('details'))) {
          $userActionLog->details .= "Current status (details),";
          $callUpdate->details = $request->get('details');
        }

        if (!empty($request->get('tags'))) {
          $trimmedTags = array_map(function ($item) {
            $item = trim($item);
            $item = strtolower($item);
            return $item;
          }, $request->get("tags"));
          $trimmedTags = array_unique($trimmedTags);
          if ($call->tags !== $trimmedTags) {
            $call->tags = " " . strtolower(implode(" | ", $trimmedTags)) . " ";
            $userActionLog->details .= " tags,";
          }
        }

        if ($request->get("current_analyst_id") !== $call->current_analyst_id && Auth::guard('tenant_api')->user()->isAllowedTo("change-analyst-for-call")) {
          $newAnalyst = TenantUser::find($request->get("current_analyst_id"));
          if (!$newAnalyst) {
            $userActionLog->details .= " new analyst not updated (not found).";
            array_push($issues, "Analyst not updated. New analyst not found.");
          } else if (!$newAnalyst->isAllowedTo('update-call')) {
            return response()->json(['message' => 'Cannot update analyst to this user as they lack the perm update-call.'], 403);
          } else {
            $userActionLog->details .= " analyst updated to " . $newAnalyst->first_name . " " . $newAnalyst->second_name . ".";
            $call->current_analyst_id = $newAnalyst->id;
          }
        } else {
          array_push($issues, "Not permitted to update analysts. Skipped.");
        }

        // If the resolved is present then that means the checkbox is checked and it is resolved.
        // Also checking to make sure it equals yes, if there is no resolved field, it means
        // the form was submitted with the resolved checkbox unticked meaning it is not solved.
        if (!empty($request->get('resolved'))) {
          if ($request->get('resolved') == 'true') {
            $call->resolved = 1;
          }
        } else {
          $call->resolved = 0;
        }

        if ($call->save()) {
          $message = "Call updated.";
          if ($callUpdate->save()) {
            $message .= " " . implode(" ", $issues);
            $message .= " Created new call update record.";
          } else {
            $message .= " Unable to create the call update record (details not saved).";
          }

          $response = response()->json(['message' => $message], 200);
        } else {
          $response = response()->json(['message' => 'Call updates not saved.'], 500);
        }

      }
    }
    return $response;
  }

  /**
   * Delete a call if there's are no dependencies.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function delete(Request $request)
  {
    $this->validate($request, [
      'call_id' => 'required|integer'
    ]);

    $call = TenantCall::find($request->get('call_id'));

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('deleted-call');

    if (!$call) {
      $response = response()->json(['message' => 'Call not found.'], 404);
    } else {
      if ($call->updates->isEmpty()) {
        if ($call->delete()) {
          if ($userActionLog->log_action_id) $userActionLog->save();
          $response = response()->json(['message' => 'Call deleted.'], 200);
        } else {
          $response = response()->json(['message' => 'Delete didn\'t work'], 500);
        }
      } else {
        $response = response()->json(['message' => 'Can\'t delete call as it has updates.'], 500);
      }
    }

    return $response;
  }

  /**
   * Retrieves the calls using simplePaginate.
   *
   * @return mixed
   */
  public function getAll(Request $request)
  {
    $this->validate($request, [
      'resolved' => 'string',
      'created_at' => 'string',
      'handled_by' => 'string',
      'form' => 'string'
    ]);
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-calls');
    $userActionLog->details = "Accessed all calls via /get/all.";

    $tenantCallQuery = TenantCall::with(['client' => function ($query) {
      $query->select(['id', 'name']);
    }]);

    $resolvedFilter = strtolower($request->get('resolved'));
    if ($resolvedFilter) {
      if ($resolvedFilter == "yes") {
        $tenantCallQuery = $tenantCallQuery->where('resolved', '=', 1);
      } else if ($resolvedFilter == "no") {
        $tenantCallQuery = $tenantCallQuery->where('resolved', '=', 0);
      }
    }

    $handledByFilter = strtolower($request->get('handled_by'));
    if ($handledByFilter) {
      if ($handledByFilter == "self") {
        $tenantCallQuery = $tenantCallQuery->where('current_analyst_id', '=', Auth::guard('tenant_api')->user()->id);
      }
    }

    $createdAtFilter = $request->get('created_at');
    if ($createdAtFilter) {
      if ($createdAtFilter == 'asc') {
        $tenantCallQuery = $tenantCallQuery->orderBy('created_at', 'asc');
      } else if ($createdAtFilter == 'desc') {
        $tenantCallQuery = $tenantCallQuery->orderBy('created_at', 'desc');
      }
    }

    $forForm = $request->get('forForm');
    if ($forForm) {
      if ($forForm == 'true') {
        $tenantCallQuery = $tenantCallQuery->get();
      }
    } else {
      $tenantCallQuery = $tenantCallQuery->simplePaginate(5);
    }

    if ($userActionLog->log_action_id) $userActionLog->save();
    return $tenantCallQuery;
  }

  /**
   * Get single call.
   *
   * @param $call_id
   * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\MessageBag
   */
  public function get($call_id)
  {
    // Validating request
    $validator = Validator::make(['call_id' => $call_id], [
      'call_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $call = TenantCall::with(['updates', 'client', 'currentAnalyst', 'receiver'])->find($call_id);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-call');

    if (empty($call)) {
      $response = response()->json(['message' => 'Call not found.'], 404);
    } else {
      $call = $call->toArray();
      $call['tags'] = explode(" | ", $call['tags']);

      $userActionLog->details = "Retrieved call " . $call['name'];
      if ($userActionLog->log_action_id) $userActionLog->save();
      $response = response()->json(['message' => 'Call found.', 'call' => $call], 200);
    }

    return $response;
  }

  /**
   * Search through calls using key word filter.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function search(Request $request)
  {
    // Validating request
    $validator = Validator::make(['query' => $request->query], [
      'query' => 'required|string'
    ]);

    $searchQuery = "% " . $request->get('query') . " %";

    $calls = TenantCall::where(function($query) use ($searchQuery)
    {
      $query->where('name', 'LIKE', $searchQuery)
        ->orWhere('tags', 'LIKE', $searchQuery);
    })->where("resolved", '=', 1)
      ->orderBy("created_at", "desc")
      ->simplePaginate()
      ->toArray();

    if (empty($calls['data'])) {
      $response = response()->json(['message' => 'No calls were found matching your criteria.'], 404);
    } else {
      $response = response()->json(['message' => 'Calls found.', 'calls' => $calls], 200);
    }

    return $response;
  }
}
