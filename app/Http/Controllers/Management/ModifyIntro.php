<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\publicTool\filterTrait;
use App\Model\Management\ModifyIntroModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ModifyIntro extends Controller
{
    //
    protected $ModifyIntroModel;


    use filterTrait;

    public function __construct(Request $request)
    {
        $this->ModifyIntroModel = new ModifyIntroModel($request);
    }

    public function modifyPage()
    {
        $result = $this->ModifyIntroModel->modifyPage();

        //返回带目前介绍页面内容的编辑器页面
        return view('tools.ueditor', [
            'title' => $result['title'],
            'previousContent' => $result['previousContent']
        ]);
    }

    public function updateIntro()
    {
        $result = $this->ModifyIntroModel->updateIntro();

        //返回修改页面，msg为1表示修改成功，2为失败
        return redirect($result['tips'])->with('msg', $result['msg']);
    }

    public function __destruct()
    {
        unset($this->ModifyIntroModel);
    }
}
