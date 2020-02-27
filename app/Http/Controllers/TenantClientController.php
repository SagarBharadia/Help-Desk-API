<?php

namespace App\Http\Controllers;

use App\TenantClient;
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

      if($client->save()) {
        $response = response()->json([], 204);
      } else {
        $response = response()->json(['message' => 'Could not save client.'], 500);
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

    public function get(int $client_id)
    {

    }
}
