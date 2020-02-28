<?php

namespace App\Http\Controllers;


use App\TenantCall;
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
        $userActionLog->details = "Created call '".$call->name."'.";

        if($call->save()) {
          $response = response()->json([], 204);
          if($userActionLog->log_action_id) $userActionLog->save();
        } else {
          $response = response()->json(['message' => 'Could not save call.'], 500);
        }

        return $response;
    }

    public function update(Request $request)
    {

    }

    public function delete(Request $request)
    {

    }

    public function getAll()
    {

    }

    public function get($call_id)
    {

    }
}
