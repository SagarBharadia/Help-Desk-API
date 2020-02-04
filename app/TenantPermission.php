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
        return $this->belongsTo('App\TenantRole');
    }

    /**
     * Get's the action of this permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permissionAction()
    {
        return $this->hasOne('App\TenantPermissionAction');
    }
}
