<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantLogAction extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'log_actions';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Get's the users which have performed this action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userActionLogs()
    {
      return $this->hasMany('App\TenantUserActionLog', 'log_action_id');
    }

    public static function getIdOfAction(string $action) {
      $action = TenantLogAction::where('action', $action)->first();
      if(!empty($action)) {
        return $action->id;
      }
      return false;
    }

}
