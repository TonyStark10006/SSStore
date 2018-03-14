<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $table = 'order';
    protected $primaryKey = 'order_id';

}
