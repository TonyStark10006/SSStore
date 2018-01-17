<?php
namespace App\Model\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetrievePasswordModel
{
    public $email;
    public $token;
    private $password;

    public function __construct(Request $request)
    {
        if ($request->has('email')) {
            $this->email = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
        }

        if ($request->has('token')) {
            $this->token = filter_var($request->input('token'), FILTER_SANITIZE_EMAIL);
        }

        if ($request->has('password')) {
            $this->password = filter_var($request->input('password'), FILTER_SANITIZE_EMAIL);
        }
    }

    private function evaluate()
    {
         $result =   DB::table('member')->where('email', $this->email)->first();
         app('debugbar')->info($result);
         if (empty($result)) {
             return [
                 'type' => 0,
                 'msg' => '用户不存在'
             ];
         } else {
             return false;
         }
    }

    private function generateToken($email)
    {
        $element = mt_rand(1000, 1000000) . 'lihaile' . $email . date('Ymd');
        return password_hash($element, PASSWORD_DEFAULT);
    }

    public function getRetrieveEmail()
    {
        if ($this->evaluate()) {
            return $this->evaluate();
        } else {
            $token =  $this->generateToken($this->email);
            $result = DB::table('member')
                ->where('email', $this->email)
                ->update([
                'token' => $token
                ]);


            if ($result) {
                return [
                    'type' => 1,
                    'msg' => $token,
                    'email' => $this->email
                ];
            }
        }
    }

    public function preResetPassword()
    {
        //验证邮箱跟token的绑定关系，并且验证token是否超期
        $result = DB::table('member')
            ->where([
                'email' => $this->email,
                'token' => $this->token
            ])
            ->first();
        app("debugbar")->info($result);
        if (empty($result)) {
            return [
                'type' => 0,
                'msg' => '请检查重置链接是否正确'
            ];
        }

        //检查token是否过期
        if (time() - strtotime($result->update_time) > 1800) {
            return [
                'type' => 0,
                'msg' => '链接已过期'
            ];
        }

        return [
            'type' => 1,
            'msg' => [
                'email' => $this->email,
                'token' => $this->token
            ]
        ];
    }

    public function resetPassword()
    {
        if (empty($this->email) || empty($this->token)) {
            return array(
                'type' => 0,
                'msg' => '链接无效'
            );
        }

        if ($this->preResetPassword()['type'] == 1) {
            //验证密码格式
            if (preg_match("/^(\w){6,20}$/", $this->password)){
                $password1 = $this->password;
            } else {
                //$this->password = false;
                return array(
                    'type' => 0,
                    'msg' => '密码由字母、数字或下划线自由组合，长度必须为6-20个字符'
                );
            }
            //更新密码
            $result1 = DB::table('member')
                ->where([
                    'email' => $this->email,
                    'token' => $this->token
                ])
                ->update([
                    'password' => md5($password1 . 'GOOD_PW'),
                    //'token' => null
                ]);
            //$result1 = $this->updateDBPassword();

            if ($result1) {
                return [
                    'type' => 1,
                    'msg' => '修改成功'
                ];
            } else {
                return [
                    'type' => 0,
                    'msg' => '修改失败'
                ];
            }
        } else {
            return [
                'type' => 0,
                'msg' => '修改失败'
            ];
        }
    }

    public function updateDBPassword(string $password, string $username) : bool
    {
        $result = DB::table('member')
            ->where('username', $username)
            ->update([
                'password' => md5($password . 'GOOD_PW')
            ]);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}