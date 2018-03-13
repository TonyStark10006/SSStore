<?php
namespace App\Http\Controllers\Management;

use Illuminate\Http\Request;

class CarouselManagement
{

    /**
     *
     * 处理上传的用于功能介绍页和路由介绍页的轮播图
     * 支持jpg, png
     * */

    private $uploadfiles;
    private $tips = '';

    public function __construct(Request $request)
    {
        $this->uploadfiles = $request->allFiles();
        //$request->file('feature_carousel_1')->get;
        app('debugbar')->info($this->uploadfiles);
    }


    public function modifyCarousel()
    {
        foreach ($this->uploadfiles as $key => $file) {
            if (!$file->isValid()) {
                $this->tips .= '文件' . $key . '文件上传出错，请重新上传' . PHP_EOL;
                continue;
            }

            app('debugbar')->info($file->extension());
            if ($file->extension() !== 'jpeg' && $file->extension() !== 'png') {
                $this->tips .= '文件' . $key . '的文件类型不正确，请按照页面提示类型选择图片' . PHP_EOL;
                continue;
                //return '上传文件格式不是jpeg或者png';
            }

            app('debugbar')->info($file->getFileName());
            $file->storeAs('Ext/Carousels/', $key . '.' . $file->extension());
        }

        if (empty($this->tips)) {
            return view('tools.ueditor', ['tips' => '修改成功', 'previousContent' => '请忽略']);
        } else {
            return view('tools.ueditor', [
                'tips' => $this->tips . PHP_EOL . '其他轮播图修改成功',
                'previousContent' => '请忽略'
            ]);
        }
    }

}