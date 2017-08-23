<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Member\RegisterModel;

class Register extends Controller
{
    //

    private $register;

    public function register(Request $request)
    {
        $this->register = new RegisterModel();
        $result = $this->register->register($request);

        return redirect('register')->with([
            'tips' => $result['tips'],
            'message' => $result['msg']
        ]);
    }

}
