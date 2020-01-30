<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

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

    public function isSuper()
    {
        return ($this->name == 'super');
    }

    public function isUser()
    {
        return ($this->name == 'user');
    }
}
