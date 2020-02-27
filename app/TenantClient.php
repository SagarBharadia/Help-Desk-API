<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantClient extends Model
{
  /**
   * The table name the model should use.
   *
   * @var string
   */
  protected $table = 'clients';

  /**
   * The connection name for the model.
   *
   * @var string
   */
  protected $connection = "tenant";

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['created_by', 'name', 'email_address', 'phone_number'];

  /**
   * Getting the user that created this company.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function createdBy()
  {
    return $this->belongsTo('App\TenantUser', 'id', 'created_by');
  }
}
