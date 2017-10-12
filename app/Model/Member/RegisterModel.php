<?php

namespace App\Model\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterModel extends Model
{

    private $registerMsg;
    private $username;
    private $password;
    private $email;
    private $invCode;


    /*
     * return array('tips' => , 'msg' => )
     *
     */
    public function register(Request $request)
    {

        $this->registerMsg = $request->all();

        //先检验邀请码是否有效
        $this->invCode = filter_var($this->registerMsg['invCode'], FILTER_SANITIZE_EMAIL);
        $result2 = DB::table('inv_code')->where('inv_code', $this->invCode)->value('valid_times');
        if (!$result2) {
            return array(
                'tips' => '邀请码无效或者超过使用次数',
                'msg' => 'register'
            );
        }

        //检查用户名是否符合规定 (两十个字符以内,只能有中文，字母，数字，下划线的)
        if(preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $this->registerMsg['username'])
            && strlen($this->registerMsg['username']) <= 20) {
            $this->username = $this->registerMsg['username'];
        } else {
            //$this->username = false;
            return array(
                'tips' => '用户名有非法字符，仅限于中英文字符，并且长度为20字符内',
                'msg' => 'register'
            );
        }

        if (preg_match("/^(\w){6,20}$/", $this->registerMsg['password'])){
            $this->password = $this->registerMsg['password'];
        } else {
            //$this->password = false;
            return array(
                'tips' => '密码由字母、数字或下划线自由组合，长度必须为6-20个字符',
                'msg' => 'register'
            );
        }


        $this->email = filter_var($this->registerMsg['email'], FILTER_SANITIZE_EMAIL);

        $result = DB::table('member')->where('username', $this->username)->value('username');
        $result3 = DB::table('member')->where('email', $this->email)->value('email');
        //判断用户名是否已经被注册
        if (!$result) {
            //判断邮箱是否已经被注册
            if (!$result3) {
                //用户名以及没被注册，写入用户信息，返回受影响行数，插入成功返回1，失败返回0
                $result1 = DB::table('member')->insert([
                    'username' => $this->username,
                    'password' => md5($this->password . 'GOOD_PW'),
                    'email' => $this->email,
                    'permission' => 2,
                    'reg_ip' => $request->getClientIp()
                ]);
                //判断是否写入成功，成功则返回提示信息以及减少一次邀请码有效次数
                if ( $result1 !== 0) {
                    //邀请码有效次数减一
                    DB::table('inv_code')->where('inv_code', $this->invCode)->update(['valid_times' => $result2 - 1]);
                    return array(
                        'tips' => '注册成功',
                        'msg' => 'login'
                    );

                } else {
                    return array(
                        'tips' => '注册异常，请重新注册',
                        'msg' => 'register'
                    );
                }
            } else {
                return array(
                    'tips' => '该邮箱已被注册',
                    'msg' => 'register'
                );
            }


        } else {
            return array(
                'tips' => '该用户名已被注册',
                'msg' => 'register'
            );
        }
    }

}
