<?php

namespace App\Http\Controllers;

use App\TenantCall;
use App\TenantClient;
use App\TenantLogAction;
use App\TenantUser;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TenantClientController extends Controller
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
   * Create a new client.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|string',
      'email_address' => 'required|email|unique:tenant.clients',
      'phone_number' => 'required|string|numeric|unique:tenant.clients|size:11'
    ]);

    $client = new TenantClient();
    $client->created_by = Auth::guard('tenant_api')->user()->id;
    $client->name = $request->get('name');
    $client->email_address = $request->get('email_address');
    $client->phone_number = $request->get('phone_number');

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-client');

    if ($client->save()) {
      if ($userActionLog->log_action_id) $userActionLog->save();
      $response = response()->json(['message' => 'Created client.'], 201);
    } else {
      $response = response()->json(['message' => 'Could not save client.'], 500);
    }

    return $response;
  }

  /**
   * Update a client.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request)
  {
    $this->validate($request, [
      'client_id' => 'integer|required',
      'name' => 'string',
      'email_address' => 'string|email|unique:tenant.clients,email_address,' . $request->get("client_id"),
      'phone_number' => 'string|size:11|unique:tenant.clients,phone_number,' . $request->get("client_id")
    ]);

    $client = TenantClient::find($request->get('client_id'));
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-client');

    if (empty($client)) {
      $response = response()->json(['message' => 'Could not find client.'], 404);
    } else {
      $userActionLog->details = "Updating details for client " . $client->name . ". Changed:";
      if (!empty($request->get('name'))) {
        $userActionLog->details .= " Name[" . $client->name . " -> " . $request->get('name') . "]";
        $client->name = $request->get('name');
      }
      if (!empty($request->get('email_address'))) {
        $userActionLog->details .= " Email Address[" . $client->email_address . " -> " . $request->get('email_address') . "]";
        $client->email_address = $request->get('email_address');
      }
      if (!empty($request->get('phone_number'))) {
        $userActionLog->details .= " Phone Number[" . $client->phone_number . " -> " . $request->get('phone_number') . "]";
        $client->phone_number = $request->get('phone_number');
      }

      if ($client->save()) {
        if ($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Updated client.'], 200);
      } else {
        $response = response()->json(['message' => 'Could not update client.'], 500);
      }
    }

    return $response;
  }

  /**
   * Delete a client if there are no dependencies on the client.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function delete(Request $request)
  {
    $this->validate($request, [
      'client_id' => 'required|integer'
    ]);

    $client = TenantClient::find($request->get('client_id'));
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('deleted-client');

    if (empty($client)) {
      $response = response()->json(['message' => 'Could not find client.'], 404);
    } else {
      if ($client->calls->isEmpty()) {
        $userActionLog->details = "Deleted client " . $client->name . "(" . $client->email_address . ")";
        if ($client->delete()) {
          if ($userActionLog->log_action_id) $userActionLog->save();
          $response = response()->json(['message' => 'Deleted client.'], 200);
        } else {
          $response = response()->json(['message' => 'Client deletion failed.'], 500);
        }
      } else {
        $response = response()->json(['message' => 'Unable to delete client as they have calls.'], 500);
      }
    }

    return $response;
  }

  /**
   * Get all the clients paginated to 15 per page.
   *
   * @return \Illuminate\Contracts\Pagination\Paginator
   */
  public function getAll(Request $request)
  {
    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-client');
    $userActionLog->details = "Retrieved all clients using /clients/get/all";

    if ($request->get("forForm") && $request->get("forForm") === "true") {
      $data = TenantClient::all();
      $userActionLog->details .= "?forForm='true'";
    } else {
      $data = TenantClient::simplePaginate();
    }

    if ($userActionLog->log_action_id) $userActionLog->save();
    return $data;
  }

  /**
   * Get a specific client.
   *
   * @param $client_id
   * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\MessageBag
   */
  public function get($client_id)
  {
    // Validating request
    $validator = Validator::make(['client_id' => $client_id], [
      'client_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $client = TenantClient::find($client_id);

    $userActionLog = new TenantUserActionLog();
    $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
    $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-client');

    if (empty($client)) {
      $response = response()->json(['message' => 'Client not found.'], 404);
    } else {
      $userActionLog->details = "Retrieved client " . $client->name;
      if ($userActionLog->log_action_id) $userActionLog->save();
      $response = response()->json(['message' => 'Client found.', 'client' => $client], 200);
    }

    return $response;
  }

  public function getCalls($client_id)
  {
    // Validating request
    $validator = Validator::make(['client_id' => $client_id], [
      'client_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $client = TenantClient::find($client_id);

    if (!$client) {
      $response = response()->json(['message' => 'Client not found.'] . 404);
    } else {
      $calls = TenantCall::where('client_id', '=', $client_id)->orderBy("created_at", "DESC")->simplePaginate();
      $response = $calls;
    }

    return $response;
  }
}
