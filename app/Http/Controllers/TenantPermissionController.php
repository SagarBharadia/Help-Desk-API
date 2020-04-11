<?php

namespace App\Http\Controllers;

use App\TenantLogAction;
use App\TenantPermissionAction;
use App\TenantUserActionLog;
use Illuminate\Support\Facades\Auth;

class TenantPermissionController extends Controller
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
   * Returns all possible permission actions.
   *
   * @return TenantPermissionAction[]|\Illuminate\Database\Eloquent\Collection
   */
    public function getAll() {

      $userActionLog = new TenantUserActionLog();
      $userActionLog->user_id = Auth::guard('tenant_api')->user()->id;
      $userActionLog->log_action_id = TenantLogAction::getIdOfAction('read-all-permissions');
      $userActionLog->details = "Retrieved all permissions using /get/all";

      if ($userActionLog->log_action_id) $userActionLog->save();

      return TenantPermissionAction::all();
    }
}
