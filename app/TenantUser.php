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
<<<<<<< Updated upstream
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

    public function isAllowedTo(string $permission)
    {
        $permissionAction = TenantPermissionAction::where('action', $permission)->first();
        if(empty($permissionAction)) return false;
        if($this->role->isRole('master')) return true;
        return $this->role->permissions->contains('permission_action_id', $permissionAction->id);
    }

    /**
     * Retrieves the role of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|mixed
     */
    public function role()
    {
        return $this->belongsTo('App\TenantRole', 'role_id');
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
     * Get the clients this user has created.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientsCreated()
    {
        return $this->hasMany('App\TenantClient', 'created_by');
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
     * Getting the current active calls for this user.
     *
     * @return mixed
     */
    public function activeCalls() {
      $calls = TenantCall::where('current_analyst_id', '=', $this->id)
        ->where('resolved', '=', 0)
        ->get();
      return $calls;
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
     * Getting the email confirmation object for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emailConfirmation()
    {
      $this->belongsTo('App\TenantUser', 'user_id');
    }

    /**
     * Returns the reset password record (if available) for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function resetPassword()
    {
      $this->hasOne('App\TenantResetPassword', 'user_id');
    }

    /**
     * Gets the action log for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actionLogs()
    {
      $this->hasMany('App\TenantUser', 'user_id');
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
=======
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

  public function isAllowedTo(string $permission)
  {
    $permissionAction = TenantPermissionAction::where('action', $permission)->first();
    if (empty($permissionAction)) return false;
    if ($this->role->isRole('master')) return true;
    return $this->role->permissions->contains('permission_action_id', $permissionAction->id);
  }

  /**
   * Retrieves the role of the user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|mixed
   */
  public function role()
  {
    return $this->belongsTo('App\TenantRole', 'role_id');
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
   * Get the clients this user has created.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function clientsCreated()
  {
    return $this->hasMany('App\TenantClient', 'created_by');
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
   * Getting the current active calls for this user.
   *
   * @return mixed
   */
  public function activeCalls()
  {
    $calls = TenantCall::where('current_analyst_id', '=', $this->id)
      ->where('resolved', '=', 0)
      ->get();
    return $calls;
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
   * Getting the email confirmation object for this user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function emailConfirmation()
  {
    return $this->belongsTo('App\TenantUser', 'user_id');
  }

  /**
   * Returns the reset password record (if available) for this user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function resetPassword()
  {
    return $this->hasOne('App\TenantResetPassword', 'user_id');
  }

  /**
   * Gets the action log for this user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function actionLogs()
  {
    return $this->hasMany('App\TenantUserActionLog', 'user_id');
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
>>>>>>> Stashed changes
}
