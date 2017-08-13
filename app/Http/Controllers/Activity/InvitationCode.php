<?php

namespace App\Http\Controllers\Activity;

use App\Model\Activity\InvitationCodeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvitationCode extends Controller
{
    //

    public function addInvCode(Request $request)
    {
        $invCodeModel = new InvitationCodeModel();
        $result = $invCodeModel->addInvCode($request);
        /*
         * 成功返回邀请码、有效次数关联数组，失败返回false
         *  array(
                'invCode' => $this->invCode,
                'invCodeTimes' => $this->invCodeTimes
            );
         *
         * */
        if ($result) {
            return redirect('activities')->with('InvCodeTips', '添加邀请码' . $result['invCode'] . '成功,有效次数：' . $result['invCodeTimes']);
        } else {
            return redirect('activities')->with('InvCodeTips', '添加邀请码失败');
        }
    }
}
