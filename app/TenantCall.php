<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantCall extends Model
{

  /**
   * The table name the model should use.
   *
   * @var string
   */
  protected $table = 'calls';

  /**
   * The connection name for the model.
   *
   * @var string
   */
  protected $connection = "tenant";

  protected $fillable = [
    'receiver_id', 'current_analyst_id', 'client_id',
    'caller_name', 'name', 'details', 'tags', 'resolved'
  ];

  /**
   * Getting the client that this call belongs to.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function client()
  {
    return $this->belongsTo('App\TenantClient', 'client_id');
  }

  /**
   * Getting the user that took this call.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function receiver()
  {
    return $this->belongsTo('App\TenantUser', 'receiver_id');
  }

  /**
   * Getting the current analyst assigned to this call.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function currentAnalyst()
  {
    return $this->belongsTo('App\TenantUser', 'current_analyst_id');
  }

  /**
   * Getting the updates for this call.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function updates()
  {
    return $this->hasMany('App\TenantCallUpdate', 'call_id', 'id');
  }
}
