<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalRole extends Model
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
    protected $connection = "global";

    /**
     * Function to return which global users have this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\GlobalUser');
    }

    /**
     * Function to see if the role of the user is that of requested.
     *
     * @param string $roleToCheck
     * @return bool
     */
    public function isRole(string $roleToCheck)
    {
        return ($this->name == $roleToCheck);
    }

}
