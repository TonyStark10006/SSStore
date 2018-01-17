<?php

namespace App\Model\Member;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class AuthModel extends Model
{
    //
    private $redirectUrl;
    private $username;
    private $password;
    private $originUsername;
    private $originPassword;
    private $oldPassword;
    private $source;
    private $userMsg;
    protected $token;

    use filterTrait;

    public function __construct(array $attributes = [], $request, $source)
    {
        parent::__construct($attributes);
        if ($request->hasCookie('R')) {
            $this->redirectUrl = urldecode(self::filter($request->cookie('R')));
        }
        $this->source = $source;

        $this->originUsername = $request->input('username');

        $this->originPassword = $request->input('password');

        $this->oldPassword = $request->input('oldPassword');
    }

    /*
     * Request Object $request
     * return array( 'tips' => , 'msg' => 'success/failure')
     *
     * */
    public function auth($request)
    {
        if(preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $this->originUsername)
            && strlen($this->originUsername) <= 20) {

            $this->username = $this->originUsername;
        }else{
            return array(
                'tips' => '请检查用户名是否输入正确',
                'msg' => 'failure'
            );
        }

        if (preg_match("/^(\w){6,20}$/", $this->originPassword)) {
            $this->password = $this->originPassword;
        }else{
            return array(
                'tips' => '请检查密码格式是否正确',
                'msg' => 'failure'
            );
        }

        //if ($request->isMethod('post')) {
        if (empty($this->username) || empty($this->password)) {
            //返回错误提示界面
            return array(
                'tips' => '请检查登录信息是否输入正确',
                'msg' => 'failure'
            );
        }
        else {
            if ($this->source == 'web') {
                if ($request->session()->has('username') && $request->session()->get('username') !== 'anonymous') {
                    //判断是否已经登录
                    return array(
                        'tips' => '当前已登录账户：' . $request->session()->get('username') . '，请先登出',
                        'msg' => 'failure'
                    );
                }
            }

            $userMessage = $this->getUserMsg($this->username);
                /*DB::table('member')
                ->select('user_id', 'username', 'email', 'user_token', 'token_expire_time', 'password', 'permission')
                ->where('username', $this->username)
                ->first();*/
            //判断用户是否存在
            if (!empty($userMessage)) {
                //校验用户输入密码与数据库密码是否一致
                if ($this->comparePassword($this->password)) {
                    //1.校验通过后web请求写session和返回跳转url
                    if ($this->source == 'web') {
                        $request->session()->put([
                            'user_id' => $userMessage->user_id,
                            'username' => $this->username,
                            'permission' => $userMessage->permission,
                            'activeTime' => time()
                        ]);

                        //判断用户原始请求域名是否为空，非空则跳转原始请求页面，空则跳转主页
                        if ($this->redirectUrl == 'logout') {
                            return array(
                                'tips' => '/',
                                'msg' => 'success'
                            );
                        } elseif (!empty($this->redirectUrl)) {
                            return array(
                                'tips' => $this->redirectUrl,
                                'msg' => 'success'
                            );
                        } else {
                            return array(
                                'tips' => '/',
                                'msg' => 'success'
                            );
                        }
                        //2.校验通过后给API请求返回token，同时每次登陆会删除旧token
                    } else {
                        $tokenModel = new TokenGeneration();
                        $this->token = $tokenModel->getToken();
                        Redis::set($this->token, json_encode($userMessage));
                        Redis::expire($this->token, 1296000);
                        DB::table('member')
                            ->where('username', $this->username)
                            ->update([
                                'user_token' => $this->token,
                                'token_expire_time' => date('Y-m-d H:i:s', strtotime("+ 15 days"))
                            ]);
                        if (!empty($userMessage->user_token)) {
                            Redis::del($userMessage->user_token);
                        }
                        return [
                            'tips' => '校验通过',
                            'msg' => 'success',
                            'token' => $this->token
                        ];
                    }

                } else {
                    return array(
                        'tips' => '密码错误',
                        'msg' => 'failure'
                    );
                }
            } else {
                return array(
                    'tips' => '用户不存在,请填写正确用户名',
                    'msg' => 'failure'
                );
            }
        }
    }

    public function getToken()
    {
        return $this->token;
    }

    public function comparePassword($password)
    {
        if (strcmp(md5($password . 'GOOD_PW'), $this->userMsg->password) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function comparePasswordForModification()
    {
        app('debugbar')->info($this->oldPassword);
        $this->sanitizePasswordOnly();
        $this->getUserMsg(Session::get('username'));
        $oldResult = $this->comparePassword($this->oldPassword);
        $newResult = $this->validatePasswordOnly();
        $newCompareResult = $this->comparePassword($this->password);

        if (!$oldResult) {
            return [
                'tips' => '原密码错误',
                'type' => false
            ];
        }

        if (!$newResult) {
            return [
                'tips' => '新密码格式错误',
                'type' => false
            ];
        }

        if ($newCompareResult) {
            return [
                'tips' => '新密码与原系统密码不能一样',
                'type' => false
            ];
        }

        return [
            'password' => $this->password,
            'username' => Session::get('username'),
            'tips' => '验证成功',
            'type' => true
        ];
    }

    public function validatePasswordOnly()
    {
        if (preg_match("/^(\w){6,20}$/", $this->originPassword)) {
            return $this->password = $this->originPassword;
        }else{
            return false;
        }
    }

    public function sanitizePasswordOnly()
    {
        $this->password = filter_var($this->originPassword, FILTER_SANITIZE_STRING);
        $this->oldPassword = filter_var($this->oldPassword, FILTER_SANITIZE_STRING);
        return;
    }

    public function getUserMsg($username)
    {
        return $this->userMsg = DB::table('member')
            ->select('user_id', 'username', 'email', 'user_token', 'token_expire_time', 'password', 'permission')
            ->where('username', $username)//
            ->first();
    }
    //}
}
