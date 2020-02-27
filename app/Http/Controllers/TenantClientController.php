<?php

namespace App\Http\Controllers;

use App\TenantClient;
use App\TenantLogAction;
use App\TenantUser;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function create(Request $request)
    {
      $this->validate($request, [
        'name' => 'required|string',
        'email_address' => 'required|string',
        'phone_number' => 'required|string|numeric'
      ]);

      $client = new TenantClient();
      $client->created_by = Auth::guard('tenant_api')->user()->id;
      $client->name = $request->get('name');
      $client->email_address = $request->get('email_address');
      $client->phone_number = $request->get('phone_number');

      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('created-client');

      if($client->save()) {
        if($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json([], 204);
      } else {
        $response = response()->json(['message' => 'Could not save client.'], 500);
      }

      return $response;
    }

    public function update(Request $request)
    {
      $this->validate($request, [
        'client_id' => 'integer',
        'name' => 'string',
        'email_address' => 'string|email',
        'phone_number' => 'string'
      ]);

      $client = TenantClient::find($request->get('client_id'));
      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('updated-client');

      if(empty($client)) {
        $response = response()->json(['message' => 'Could not find client.'],404);
      } else {
        $userActionLog->details = "Updating details for client ".$client->name.". Changed:";
        if(!empty($request->get('name'))) {
          $userActionLog->details .= " Name[".$client->name." -> ".$request->get('name')."]";
          $client->name = $request->get('name');
        }
        if(!empty($request->get('email_address'))) {
          $userActionLog->details .= " Email Address[".$client->email_address." -> ".$request->get('email_address')."]";
          $client->email_address = $request->get('email_address');
        }
        if(!empty($request->get('phone_number'))) {
          $userActionLog->details .= " Phone Number[".$client->phone_number." -> ".$request->get('phone_number')."]";
          $client->phone_number = $request->get('phone_number');
        }

        if($client->save()) {
          if($userActionLog->log_action_id) $userActionLog->save();
          $response = response()->json([], 204);
        } else {
          $response = response()->json(['message' => 'Could not update client.'], 500);
        }
      }

      return $response;
    }

    public function delete(Request $request)
    {
      $this->validate($request, [
        'client_id' => 'required|integer'
      ]);

      $client = TenantClient::find($request->get('client_id'));
      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('deleted-client');

      if(empty($client)) {
        $response = response()->json(['message' => 'Could not find client.'], 404);
      } else {
        if(empty($client->calls)) {
          $userActionLog->details = "Deleted client ".$client->name."(".$client->email_address.")";
          if($client->delete()) {
            if($userActionLog->log_action_id) $userActionLog->save();
            $response = response()->json([], 204);
          } else {
            $response = response()->json(['message' => 'Client deletion failed.'], 500);
          }
        } else {
          $response = response()->json(['message' => 'Unable to delete client as they have dependencies.'], 500);
        }
      }

      return $response;
    }

    public function getAll()
    {
      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-client');
      $userActionLog->details = "Retrieved all clients using /clients/get/all";
      if($userActionLog->log_action_id) $userActionLog->save();
      return DB::connection('tenant')->table('clients')->simplePaginate();
    }

    public function get($client_id)
    {
      // Validating request
      $validator = Validator::make(['client_id' => $client_id], [
        'client_id' => 'required|integer'
      ]);

      if($validator->fails()) return $validator->errors();

      $client = TenantClient::find($client_id);

      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('accessed-client');

      if(empty($client)) {
        $response = response()->json(['message' => 'Client not found.'], 404);
      } else {
        $userActionLog->details = "Retrieved client ".$client->name;
        if($userActionLog->log_action_id) $userActionLog->save();
        $response = response()->json(['message' => 'Client found.', 'client' => $client], 200);
      }

      return $response;
    }
}
