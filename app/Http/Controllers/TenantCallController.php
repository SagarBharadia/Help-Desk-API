<?php

namespace App\Http\Controllers;


use App\TenantCall;
use Illuminate\Http\Request;

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
            'company_id' => 'required|integer',
            'caller_name' => 'required|string',
            'name' => 'required|string',
            'details' => 'required|string',
            'tags' => 'required|string'
        ]);

        // Creating the call using the TenantCall model
        return TenantCall::create([
            'receiver_id' => $request->user()->id,
            'current_analyst_id' => 0,
            'company_id' => $request->company_id,
            'caller_name' => $request->caller_name,
            'name' => $request->name,
            'details' => $request->details,
            'tags' => $request->tags,
            'resolved' => 0
        ]);
    }
}
