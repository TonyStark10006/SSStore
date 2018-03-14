<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    //
    const CREATED_AT = 'reg_date';
    const UPDATED_AT = 'update_time';
    protected $table = 'member';
    protected $primaryKey = 'user_id';

}
