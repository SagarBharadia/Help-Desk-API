<?php

namespace App\Http\Controllers;


use App\TenantCall;
use App\TenantCallUpdate;
use App\TenantLogAction;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
      'tags' => 'required|string'
    ]);

    $call = new TenantCall();
    $call->receiver_id = Auth::guard('tenant_api')->user()->id;
    $call->current_analyst_id = 0;
    $call->client_id = $request->get('client_id');
    $call->caller_name = $request->get('caller_name');
    $call->name = $request->get('name');
    $call->details = $request->get('details');
    $call->tags = $request->get('tags');
    $call->resolved = 0;

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-call');
    $userActionLog->details = "Created call '" . $call->name . "'.";

    if ($call->save()) {
      $response = response()->json([], 204);
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
      'tags' => 'string',
      'resolved' => 'string'
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
        $response = response()->json(['message' => 'Not allowed.'], 403);
      } else {
        $callUpdate = new TenantCallUpdate();
        $callUpdate->user_id = Auth::guard('tenant_api')->user()->id;
        $callUpdate->call_id = $call->id;

        $userActionLog->details = "Updated call '" . $call->name . "'. Updated: ";

        if (!empty($request->get('details'))) {
          $userActionLog->details .= "Current status (details),";
          $callUpdate->details = $request->get('details');
        }

        if (!empty($request->get('tags'))) {
          $userActionLog->details .= " tags,";
          $call->tags = $request->get('tags');
        }

        // If the resolved is present then that means the checkbox is checked and it is resolved.
        // Also checking to make sure it equals yes, if there is no resolved field, it means
        // the form was submitted with the resolved checkbox unticked meaning it is not solved.
        if (!empty($request->get('resolved'))) {
          if ($request->get('resolved') == 'yes') {
            $call->resolved = 1;
          }
        } else {
          $call->resolved = 0;
        }

        if ($call->save()) {
          $message = "Call updated.";
          if ($callUpdate->save()) {
            $message .= " Created new call update record.";
          } else {
            $message .= " Unable to create the call update record (details not saved).";
          }
          $userActionLog->save();

          $response = response()->json(['message' => $message], 204);
        } else {
          $response = response()->json(['message' => 'Call updates not saved.'], 500);
        }

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

  public function get($call_id)
  {
    return response()->json([], 501);
  }
}
