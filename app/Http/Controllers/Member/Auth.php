<?php

namespace App\Http\Controllers\Member;

use App\Model\Member\AuthModel;
use App\Model\Member\TokenGeneration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Auth extends Controller
{
    //
    private $auth;

    public function auth(Request $request)
    {
        //return array('tips' => 'success/failure', 'msg' => URL/tips)
        $this->auth = new AuthModel([], $request, 'web');
        $result = $this->auth->auth($request);

        if ($result['msg'] == 'success') {
            return redirect($result['tips']);
        } else {
            return redirect('login')->with('tips', $result['tips']);
        }
    }

    public function authForAPI(Request $request)
    {
        $this->auth = new AuthModel([], $request, 'api');
        $result = $this->auth->auth($request);
        return response()->json($result, 200, [], 256);
    }

    public function test(Request $request)
    {
        return response()->json(['good' => '我擦', 'fail' => '我的天', 'url' => $request->path()], 200, [], 256);
    }
}
