<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class Introduction extends Model
{
    //
    protected $table = 'introduction';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

}
