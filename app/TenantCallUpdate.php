<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantCallUpdate extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'call_updates';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Getting the user that performed this update.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\TenantUser', 'id', 'user_id');
    }

    /**
     * Getting this call that this update belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call()
    {
        return $this->belongsTo('App\TenantCall', 'id', 'call_id');
    }


}
