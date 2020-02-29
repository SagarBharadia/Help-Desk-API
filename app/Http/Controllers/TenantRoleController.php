<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantRoleController extends Controller
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

  public function create(Request $request) {
    return response()->json([], 501);
  }

  public function update(Request $request) {
    return response()->json([], 501);
  }

  public function delete(Request $request) {
    return response()->json([], 501);
  }

  public function getAll() {
    return response()->json([], 501);
  }

  public function get($role_id) {
    return response()->json([], 501);
  }
}
