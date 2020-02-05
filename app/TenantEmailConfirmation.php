<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantEmailConfirmation extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'email_confirmations';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Get's the user which this email confirmation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
