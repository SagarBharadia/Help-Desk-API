<?php

namespace App\Http\Controllers;

use App\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantUserController extends Controller
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
      // validate the request
      $this->validate($request, [
        'role_id' => 'required|integer',
        'first_name' => 'required|string',
        'second_name' => 'required|string',
        'email_address' => 'required|email|unique:users',
        'password' => 'required|confirmed'
      ]);

      $user = new TenantUser();
      $user->role_id = $request->get('role_id');
      $user->first_name = $request->get('first_name');
      $user->second_name = $request->get('second_name');
      $user->email_address = $request->get('email_address');
      $user->password = Hash::make($request->password);

      if($user->save()) {
        $response = response()->json(['message' => 'User created.'], 201);
      } else {
        $response = response()->json(['message' => 'User failed to save.'], 500);
      }

      return $response;
    }

    public function update(Request $request)
    {

    }

    public function deactivate(Request $request)
    {

    }

    public function getAll()
    {

    }

    public function getUser(int $userId)
    {

    }
}
