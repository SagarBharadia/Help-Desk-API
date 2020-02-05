<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenantCompany extends Model
{
    /**
     * The table name the model should use.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = "tenant";

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
