<?php
namespace App\Model\DB\DBQuery;

use Illuminate\Support\Facades\DB;

class DBQuery
{
    public function getAllWithAKey($table, $key, $keyValue)
    {
        return DB::table($table)->where($key, $keyValue)->get();
    }

    //
    public function getAValueWithConditions(string $table, array $condition, string $value)
    {
        return DB::table($table)->where($condition)->value($value);
    }

    public function getSelectedWithAKey($table, $key, $keyValue, $selected)
    {
        return DB::table($table)->where($key, $keyValue)->select($selected)->get();
    }

}