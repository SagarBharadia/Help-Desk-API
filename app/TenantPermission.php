<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantPermission extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'permissions';

  /**
   * What relations should be returned with the user.
   *
   * @var array
   */
    protected $with = ['permissionAction'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Get's the role in which this permission belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\TenantRole', 'role_id');
    }

    /**
     * Get's the action of this permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permissionAction()
    {
        return $this->belongsTo('App\TenantPermissionAction', 'permission_action_id');
    }
}
