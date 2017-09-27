<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Member\RetrievePasswordModel;
use App\Jobs\SendEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetrievePassword as mailBlade;

class RetrievePassword extends Controller
{
    //
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function retrievePassword()
    {
        $model = new RetrievePasswordModel($this->request);
        $result = $model->getRetrieveEmail();
        if ($result['type'] == 0) {
            return redirect('retrieve-password')->with('tips', $result['msg']);
        }

        if ($result['type'] == 1) {
            //发送邮件
            dispatch(new SendEmail(
                    $result['email'],
                    array(
                        3,//邮件类型，下单成功通知邮件类型为1
                        $result['msg'])
                    ));
            //Mail::to($result['email'])->send(new mailBlade($result['email'], $result['token']));
            return redirect('retrieve-password')->with([
                'tips' => '重置密码邮件已发送到' . $result['email']
            ]);
        }

        return view('errors.404');
    }

    public function preResetPassword()
    {
        //
        $model = new RetrievePasswordModel($this->request);
        $result = $model->preResetPassword();
        //处理异常
        if ($result['type'] == 0) {
            return redirect('retrieve-password')->with([
                'tips' => $result['msg']
            ]);
        }

        if ($result['type'] == 1) {
            return view('member.passwordReset', [
                'email' => $result['msg']['email'],
                'token' => $result['msg']['token']
            ]);
        }
    }

    public function resetPassword()
    {
        $model = new RetrievePasswordModel($this->request);
        $result = $model->resetPassword();
        return redirect('retrieve-password')->with([
            'tips' => $result['msg']
        ]);
    }
}
