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
     * Get's the global user that created this company as a tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User');
    }
}