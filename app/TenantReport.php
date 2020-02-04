<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantReport extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

    /**
     * Retrieves the user that created this report.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\TenantUser',  'id', 'created_by');
    }
}
