<?php

namespace App\Model\Member;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthModel extends Model
{
    //
    private $redirectUrl;
    private $username;
    private $password;

    use filterTrait;

    public function __construct(array $attributes = [], $request)
    {
        parent::__construct($attributes);
        $this->redirectUrl = urldecode(self::filter($request->cookie('R')));

    }

    /*
     * Request Object $request
     * return array( 'tips' => , 'msg' => 'success/failure')
     *
     * */
    public function auth($request)
    {
        if(preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $request->input('username'))
            && strlen($request->input('username')) <= 20) {

            $this->username = $request->input('username');
        }else{
            return array(
                'tips' => '请检查用户名是否输入正确',
                'msg' => 'failure'
            );
        }

        if (preg_match("/^(\w){6,20}$/", $request->input('password'))) {
            $this->password = $request->input('password');
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
            if ($request->session()->has('username') && $request->session()->get('username') !== 'anonymous') {
                //判断是否已经登录
                return array(
                    'tips' => '当前已登录账户：' . $request->session()->get('username') . '，请先登出',
                    'msg' => 'failure'
                );

            } else {
                $userMessage = DB::table('member')->where('username', $this->username)->first();
                //判断用户是否存在
                if (!empty($userMessage)) {
                    //校验用户输入密码与数据库密码是否一致
                    if (strcmp(md5($this->password.'GOOD_PW'), $userMessage->password) == 0) {
                        //赋值并跳转
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
    }
    //}
}
