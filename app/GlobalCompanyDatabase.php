<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalCompanyDatabase extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'global';

    /**
     * The table the model should use.
     *
     * @var string
     */
    protected $table = 'company_databases';

    /**
     * Get's the global user that created this company as a tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\GlobalUser', "global_user_id");
    }
}