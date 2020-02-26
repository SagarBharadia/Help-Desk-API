<?php

namespace App\Http\Controllers;

use App\Rules\StrongPassword;
use App\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

  /**
   * Create a new tenant user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    // validate the request
    $this->validate($request, [
      'role_id' => 'required|integer',
      'first_name' => 'required|string',
      'second_name' => 'required|string',
      'email_address' => 'required|email|unique:users',
      'password' => ['string', 'required', 'confirmed', new StrongPassword]
    ]);

    $user = new TenantUser();
    $user->role_id = $request->get('role_id');
    $user->first_name = $request->get('first_name');
    $user->second_name = $request->get('second_name');
    $user->email_address = $request->get('email_address');
    $user->password = Hash::make($request->get('password'));

    if ($user->save()) {
      $response = response()->json(['message' => 'User created.'], 201);
    } else {
      $response = response()->json(['message' => 'User failed to save.'], 500);
    }

    return $response;
  }

  /**
   * Update a tenant user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request)
  {
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer',
      'role_id' => 'integer',
      'first_name' => 'string',
      'second_name' => 'string',
      'email_address' => 'email|unique:users',
      'password' => ['string', 'confirmed', new StrongPassword]
    ]);

    $user = TenantUser::find($request->get('user_id'));

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      if(!empty($request->get('role_id'))) $user->role_id = $request->get('role_id');
      if(!empty($request->get('first_name'))) $user->first_name = $request->get('first_name');
      if(!empty($request->get('second_name'))) $user->second_name = $request->get('second_name');
      if(!empty($request->get('email_address'))) $user->email_address = $request->get('email_address');
      if(!empty($request->get('password'))) $user->password = Hash::make($request->get('password'));

      if($user->save()) {
        $response = response()->json(['message' => 'User updated.'], 204);
      } else {
        $response = response()->json(['message' => 'User updates could not be saved.'], 500);
      }
    }

    return $response;
  }

  /**
   * Toggle the active state of a user.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function toggleActive(Request $request)
  {
    // Validate the request
    $this->validate($request, [
      'user_id' => 'required|integer'
    ]);

    $user = TenantUser::find($request->get('user_id'));

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $user->active = !$user->active;

      if($user->save()) {
        $response = response()->json(['message' => 'User updated.'], 204);
      } else {
        $response = response()->json(['message' => 'Could not toggle active state of user.'], 500);
      }
    }

    return $response;
  }

  /**
   * Retrieve users in pagination, including links to get the next page.
   *
   * @return \Illuminate\Contracts\Pagination\Paginator
   */
  public function getAll()
  {
    return DB::connection('tenant')->table('users')->select(['id', 'first_name', 'second_name', 'email_address', 'active'])->simplePaginate();
  }

  /**
   * Get user based on user id.
   *
   * @param int $user_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function getUser(int $user_id)
  {
    // Validating request
    $validator = Validator::make(['user_id' => $user_id], [
      'user_id' => 'required|integer'
    ]);

    $user = TenantUser::find($user_id);

    if(empty($user)) {
      $response = response()->json(['message' => 'User not found.'], 404);
    } else {
      $response = response()->json(['message' => 'User found.', 'user' => $user], 200);
    }

    return $response;
  }
}
