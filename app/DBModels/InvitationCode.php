<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class InvitationCode extends Model
{
    //
    protected $table = 'inv_code';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

}
