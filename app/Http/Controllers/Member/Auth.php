<?php

namespace App\Http\Controllers\Member;

use App\Model\Member\AuthModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Auth extends Controller
{
    //
    private $auth;

    public function auth(Request $request)
    {
        //return array('tips' => 'success/failure', 'msg' => URL/tips)
        $this->auth = new AuthModel([], $request);
        $result = $this->auth->auth($request);

        if ($result['msg'] == 'success') {
            return redirect($result['tips']);
        } else {
            return redirect('login')->with('tips', $result['tips']);
        }
    }
}
