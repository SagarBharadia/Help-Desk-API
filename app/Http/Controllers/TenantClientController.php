<?php

namespace App\Http\Controllers;

use App\TenantClient;
use App\TenantLogAction;
use App\TenantUser;
use App\TenantUserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $userActionLog->save();
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
          $userActionLog->save();
          $response = response()->json([], 204);
        } else {
          $response = response()->json(['message' => 'Could not update client.'], 500);
        }
      }

      return $response;
    }

    public function delete(Request $request)
    {

    }

    public function getAll()
    {

    }

    public function get(int $client_id)
    {

    }
}
