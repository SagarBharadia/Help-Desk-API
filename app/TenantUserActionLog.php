<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantUserActionLog extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'user_action_logs';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Get's the action of this user action log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logAction()
    {
        return $this->belongsTo('App\TenantLogAction');
    }

    /**
     * Get's the user of this user action log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\TenantUser', 'id', 'user_id');
    }

}
