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
}