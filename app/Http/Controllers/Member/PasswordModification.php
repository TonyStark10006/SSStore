<?php
/**
 * Created by PhpStorm.
 * User: vofy
 * Date: 2018/1/15
 * Time: 下午4:05
 */
namespace APP\Http\Controllers\Member;

use App\Model\Member\AuthModel;
use App\Model\Member\RetrievePasswordModel;
use Illuminate\Http\Request;

class PasswordModification
{
    public function modifyPassword(Request $request)
    {
        $model = new AuthModel([], $request, 'web');
        $result = $model->comparePasswordForModification();
        if ($result['type']) {
            $model1 = new RetrievePasswordModel($request);
            if ($model1->updateDBPassword($result['password'], $result['username'])) {
                return '修改成功';
            } else {
                return '修改失败';
            }
        } else {
            return $result['tips'];
        }
    }
}