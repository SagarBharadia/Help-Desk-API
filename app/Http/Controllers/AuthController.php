<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class AuthController extends Controller
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
     * Store a new user.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        // validate the request
        $this->validate($request, [
            'first_name' => 'required|string',
            'second_name' => 'required|string',
            'email_address' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        try
        {
            $user = new User;
            $user->first_name = $request->input('first_name');
            $user->second_name = $request->input('second_name');
            $user->email_address = $request->input('email_address');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            // return successful user response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
        } catch (\Exception $e)
        {
            // return error message
            return response()->json(['message' => 'User registration failed!'], 409);
        }
    }
}
