<?php

namespace App\Http\Controllers\Management;

use App\DBModels\Introduction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class IntroductionManagement extends Controller
{
    //
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // 返回修改页面
    public function index()
    {
        $modifyTarget = $this->request->input('modify');
        if ($modifyTarget == '1') {
            return view('tools.ueditor', [
                'title' => '修改功能介绍内容',
                'previousContent' => Redis::get('feature_intro'),
                'target' => 1
            ]);
        }

        if ($modifyTarget == '2') {
            return view('tools.ueditor', [
                'title' => '修改路由介绍内容',
                'previousContent' => Redis::get('route_intro'),
                'target' => 2
            ]);
        }

        return view('tools.ueditor', [
            'title' => '传入参数错误',
            'previousContent' => '传入参数错误',
            'target' => 0
        ]);
    }

    // 更新展示内容
    public function update()
    {
        $model = new Introduction();
        $updateTarget = $this->request->input('type');
        $updateContent = preg_replace("/<script[^>]*?>.*?<\/script>/si","",
            html_entity_decode($this->request->input('editorValue')));
        $model->author_user_id = Session::get('user_id');
        $redirectUrl = Session::get('_previous')['url'];

        if ($updateTarget == '1') {
            $model->type = 1;
            $model->content = $updateContent;
            $result1 = $model->save();
            $result2 = Redis::set('feature_intro', $updateContent);
        }

        if ($updateTarget == '2') {
            $model->type = 2;
            $model->content = $updateContent;
            $result1 = $model->save();
            $result2 = Redis::set('route_intro', $updateContent);
        }

        if (isset($result1, $result2)) {
            if ($result1 && $result2) {
                return redirect($redirectUrl)->with('msg', 1);
            } else {
                return redirect($redirectUrl)->with('msg', 0);
            }
        } else {
            Log::error('更新目标输入参数有误');
            return redirect($redirectUrl)->with('msg', 0);
        }


    }
}
