<?php

namespace App\Model\Management;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class ModifyIntroModel
{
    /*
     * 功能介绍跟线路介绍内容更新功能
     * redis保存，打开网页时读取redis数据
     * mysql仅仅保存修改记录
     *
     * */

    //
    private $type;
    private $newContent;
    private $previousContent;
    private $modifyTarget;
    private $returnPath;


    use filterTrait;

    public function __construct(Request $request)
    {
        if ($request->has('type')) {
            $this->type = $this->filter($request->input('type'));

        }

        if ($request->has('modify')) {
            $this->modifyTarget = $this->filter($request->input('modify'));
            //查询当前功能介绍信息
            if ($this->modifyTarget == '1') {
                $this->previousContent = $this->removeScript(Redis::get('feature_intro'));
            }

            //查询当前路由介绍信息
            if ($this->modifyTarget == '2') {
                $this->previousContent = $this->removeScript(Redis::get('route_intro'));
            }
        }


        if ($request->has('editorValue') && $request->input('editorValue') !== '') {
            $this->newContent = html_entity_decode(
                                    $this->filter(
                                        $this->removeScript(
                                            $request->input('editorValue')
                                        )
                                    )
                                );
        } else {
            $this->newContent = null;
        }
    }

    public function modifyPage()
    {
        return view('tools.ueditor', [
            'title' => '修改展示内容',
            'previousContent' => $this->previousContent
        ]);
    }

    public function updateIntro()
    {
        $this->returnPath = Session::get('_previous')['url'];

        //mysql保存修改记录
        $resultDB = DB::table('introduction')
            ->insert([
                'author_user_id' => Session::get('user_id'),
                'type' => $this->type,
                'content' => $this->newContent
            ]);

        if ($this->type == '1') {
            //Redis门面返回true或者false
            $resultRedis = Redis::set('feature_intro', $this->newContent);
        } else {
            $resultRedis = Redis::set('route_intro', $this->newContent);
        }


        if ($resultDB && $resultRedis) {
            return [
                'tips' => $this->returnPath,
                'msg' => 1
            ];
        } else {
            return [
                'tips' => $this->returnPath,
                'msg' => 2
            ];
        }
    }
}
