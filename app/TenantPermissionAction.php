<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantPermissionAction extends Model
{
  /**
   * The table name the model should use.
   *
   * @var string
   */
  protected $table = 'permission_actions';

  /**
   * The connection name for the model.
   *
   * @var string
   */
  protected $connection = "tenant";

  /**
   * Get's the permissions in which this action has been used.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function permissions()
  {
    return $this->hasMany('App\TenantPermission', 'permission_action_id');
  }

  public static function getByAction(string $action)
  {
    return TenantPermissionAction::where('action', '=', $action)->first();
  }

}
