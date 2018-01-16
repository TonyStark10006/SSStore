<?php
/**
 * Created by PhpStorm.
 * User: vofy
 * Date: 2018/1/15
 * Time: 下午4:05
 */
namespace APP\Http\Member;

use App\Model\Member\AuthModel;
use App\Model\Member\RetrievePasswordModel;
use Illuminate\Http\Request;

class PasswordModification
{
    public function modifyPassword(Request $request)
    {
        $model = new AuthModel([], $request, 'web');
        if ($model->comparePassword()) {
            $model1 = new RetrievePasswordModel($request);
            if ($model1->updateDBPassword()) {
                return '修改成功';
            } else {
                return '修改失败';
            }
        } else {
            return '原密码错误';
        }
    }
}