<?php

namespace App\DBModels;

use Illuminate\Database\Eloquent\Model;

class NodeList extends Model
{
    //
    protected $table = 'node_list';
    protected $primaryKey = 'zone_id';
    const UPDATED_AT = 'update_time';
}
