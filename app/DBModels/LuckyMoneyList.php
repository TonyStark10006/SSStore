<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class LuckyMoneyList extends Model
{
    //
    protected $table = 'lm_list';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}
