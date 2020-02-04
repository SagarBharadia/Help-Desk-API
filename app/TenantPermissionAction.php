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
    protected $table = 'permissions';

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
        return $this->hasMany('App\TenantPermission');
    }

}
