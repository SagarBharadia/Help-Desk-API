<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantRole extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Function to return which global users have this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\TenantUser');
    }

    /**
     * Function to see if the users role is that of the one specified.
     *
     * @return bool
     */
    public function isRole(string $roleToCheck)
    {
        return ($this->name == $roleToCheck);
    }

    /**
     * Get's the permissions for the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany('App\TenantPermission');
    }
}
