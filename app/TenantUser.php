<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class TenantUser extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
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
    protected $connection = "tenant";

    /**
     * Retrieves the role of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|mixed
     */
    public function role()
    {
        return $this->belongsTo('App\TenantRole');
    }

    /**
     * Get the reports the user has generated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports()
    {
        return $this->hasMany('App\TenantReport', 'created_by');
    }

    /**
     * Get the companies this user has created.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companiesCreated()
    {
        return $this->hasMany('App\TenantCompany', 'created_by');
    }

    /**
     * Getting the calls that this user has received.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedCalls()
    {
        return $this->hasMany('App\TenantCall', 'receiver_id');
    }

    /**
     * Getting the current calls this user is handling.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function currentCalls()
    {
        return $this->hasMany('App\TenantCall', 'current_analyst_id');
    }

    /**
     * Getting the call updates this user has performed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function callUpdates()
    {
        return $this->hasMany('App\TenantCallUpdate', 'user_id');
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
