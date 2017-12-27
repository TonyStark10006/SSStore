<?php

namespace App\Http\Controllers\Int;

use App\Http\Controllers\publicTool\filterTrait;
use App\Model\Management\Stock\StockQueryModel;
use App\Model\Services\UsageSummary;
use App\Traits\APIMsg;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class Resource extends Controller
{
    //
    private $request;
    private $type;


    use filterTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->type = $this->filter($request->input('type'));
    }

    public function go()
    {
        if ($this->type == 'orderstock') {
            return self::getIRealTimeStock();
        }

        if ($this->type == 'sevendaysflow') {
            return self::getSevenDaysFlow();
        }

        if ($this->type == 'threemonthsflow') {
            return self::getThreeMonthsFlow();
        }

        if ($this->type == 'getWOAttachment') {
            return self::getImg();
        }

        if ($this->type == 'getOrderSummarySheet') {
            return self::getOrderSummarySheet($this->request);
        }

        return view('errors\404');
    }

    public function getIRealTimeStock()
    {
        $model = new StockQueryModel($this->request);
        return $model->realTimeStockQuery();
    }

    public function getSevenDaysFlow()
    {
        $model = new UsageSummary($this->request);
        return $model->getSevenDaysFlow();
    }

    public function getThreeMonthsFlow()
    {
        $model = new UsageSummary($this->request);
        return $model->getThreeMonthsFlow();
    }

    public function getImg()
    {
        $SSWorkOrderID = filter_var($this->request->input('id'), FILTER_SANITIZE_STRING);
        $filePath = '../storage/app/WorkOrder/attachment/' . Session::get('username') . '/' . $SSWorkOrderID . '.jpg';
        if (file_exists($filePath)) {
            $img = file_get_contents($filePath);
            return response($img,'200')
                ->header('Content-Type', 'image/jpeg');
        } else {
            return view('errors\404');
        }

    }

    public function getOrderSummarySheet(Request $request)
    {
        $filename = filter_var($request->input('filename'), FILTER_SANITIZE_STRING);
        $filePath = './../storage/app/Statistics/OrderSummary/' . $filename;
        app('debugbar')->info($filename, $filePath);
        if (file_exists($filePath)) {
            /*header('Content-Description: File Transfer');
                //描述页面返回的结果
                header('Content-Type: application/octet-stream');//返回内容的类型，此处只知道是二进制流。具体返回类型可参考http://tool.oschina.net/commons
                header('Content-Disposition: attachment; filename="' . substr($filename, 0, -5) . '"');//可以让浏览器弹出下载窗口
                //header('Content-Transfer-Encoding: binary');//内容编码方式，直接二进制，不要gzip压缩
                header('Expires: 0');//过期时间
                header('Cache-Control: must-revalidate');//缓存策略，强制页面不缓存，作用与no-cache相同，但更严格，强制意味更明显
                header('Pragma: public');
                header('Content-Length: ' . filesize($filename));//文件大小，在文件超过2G的时候，filesize()返回的结果可能不正确;
            //set_time_limit(0);
            readfile($filePath);*/
            return response()->download($filePath);
        } else {
            return view('errors\404');
        }
    }

    use APIMsg;
    public function getUserZoneMsgForAPI(Request $request)
    {
        $userMsg = json_decode(Redis::get($request->input('token')), true);
        $userZoneMsg = json_decode(
            json_encode(DB::table('user')
            ->select('node_name', 'user_name', 'uid', 'email', 'passwd', 'port', 'protocol', 'obfs', 'enable',
            'method', 'expire_time')
            ->where('uid', $userMsg['user_id'])
            ->get()
        ), true);

        return response()->json(
            array_merge($this->success, ['data' => $userZoneMsg]),
            200, [], 256);
    }

    public function getAppIndexContent()
    {
        $content = Redis::get('appIndexContent');
        //$content = DB::table('introduction')->where('type', 2)->orderBy('create_time', 'desc')->first();//descent ascent
        return $this->mergeResponse($this->success, $content);
    }
}
