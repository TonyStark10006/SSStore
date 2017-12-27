<?php
namespace App\Model\DB\DBInsert;

use Illuminate\Support\Facades\DB;

class DBInsert
{
    public function insertANArray(string $table, array $data)
    {
        return DB::transaction(function () use ($table, $data) {
            return DB::table($table)->insert($data);
        }, 3);
    }

}