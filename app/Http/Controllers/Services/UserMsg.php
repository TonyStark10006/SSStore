<?php
/**
 * Created by PhpStorm.
 * User: vofy
 * Date: 2018/1/16
 * Time: 上午11:42
 */
namespace App\Http\Controllers\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserMsg
{
    //

    public function getUserMsg()
    {
        $username = Session::get('username');
        $profile = DB::table('member')
            ->where('username' , $username)
            ->first();
        //$usage = DB::table('usage_status')->where('user_id', Session::get('user_id'))->get();
        //print_r($profile);
        //stdClass Object ( [user_id] => 1 [permission] => [username] => test [password] => c4a7f16fa283bdfcb37b7d4f3512133a [user_group] => ultimate [remark] => [reg_date] => 2017-05-04 11:55:50 [email] => szetowh@qq.com )
        return view('profile', [
            'profile' => $profile,
            'title' => '个人资料',
            //'usage' => $usage
        ]);
    }
}