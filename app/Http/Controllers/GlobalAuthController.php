<?php

namespace App\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use App\GlobalUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class GlobalAuthController extends Controller
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
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function register(Request $request)
  {
    // validate the request
    $this->validate($request, [
      'first_name' => 'required|string',
      'second_name' => 'required|string',
      'email_address' => 'required|email|unique:users',
      'password' => ['string', 'required', 'confirmed', new StrongPassword]
    ]);

    try {
      $user = new GlobalUser;
      $user->first_name = $request->input('first_name');
      $user->second_name = $request->input('second_name');
      $user->email_address = $request->input('email_address');
      $plainPassword = $request->input('password');
      $user->password = Hash::make($plainPassword);

      $user->save();

      // return successful user response
      return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
    } catch (\Exception $e) {
      // return error message
      return response()->json(['message' => 'Global user registration failed!'], 409);
    }
  }

  /**
   * Attempting to login.
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
    if (!$token = Auth::guard('global_api')->attempt($credentials)) {
      $response = response()->json(['message' => 'Unauthorized.'], 401);
    } else {
      $user = Auth::guard('global_api')->user();
      $response = response()->json([
        'token' => $token,
        'token_type' => 'bearer',
        'expires_in' => Auth::factory()->getTTL(),
        'company_subdir' => "super",
        'company_name' => "Super",
        'user_name' => $user->first_name . " " . $user->second_name
      ], 200);
    }

    // Else return with token
    return $response;

  }

}
