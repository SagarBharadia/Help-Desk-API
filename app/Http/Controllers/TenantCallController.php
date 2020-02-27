<?php

namespace App\Http\Controllers;


use App\TenantCall;
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

        // Creating the call using the TenantCall model
        return TenantCall::create([
            'receiver_id' => Auth::guard('tenant_api')->user()->id,
            'current_analyst_id' => 0,
            'client_id' => $request->get('client_id'),
            'caller_name' => $request->get('caller_name'),
            'name' => $request->get('name'),
            'details' => $request->get('details'),
            'tags' => $request->get('tags'),
            'resolved' => 0
        ]);
    }
}
