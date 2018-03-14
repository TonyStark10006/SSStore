<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class LuckyMoneyListFetched extends Model
{
    //
    protected $table = 'lm_list_fetched';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}
