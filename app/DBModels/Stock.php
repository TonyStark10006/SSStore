<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'stock';
    const UPDATED_AT = 'update_time';
    public $timestamps = false;
}
