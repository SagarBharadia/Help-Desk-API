<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class GlobalUser extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'second_name', "email_address", "role_id"
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "global";

    /**
     * Retrieves the role of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|mixed
     */
    public function role()
    {
        return $this->belongsTo('App\GlobalRole');
    }

    /**
     * Get's the global companies the user has created.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companiesCreated()
    {
        return $this->hasMany('App\GlobalCompanyDatabase');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
