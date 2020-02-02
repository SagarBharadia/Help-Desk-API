<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TenantAuthController extends Controller
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
     * Attempt to find a user with the credentials provided.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // validate the request
        $this->validate($request, [
          'email_address' => 'required|string',
          'password' => 'required|string'
        ]);

        $credentials = $request->only(['email_address', 'password']);

        // Attempting to login
        if(! $token = Auth::guard('tenant_api')->attempt($credentials)) {
            $response =  response()->json(['message' => 'Unauthorized.'], 401);
        } else {
            $response = $this->respondWithToken($token);
        }

        // Else return with token
        return $response;

    }

}
