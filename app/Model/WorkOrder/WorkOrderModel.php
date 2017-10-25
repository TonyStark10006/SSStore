<?php
namespace App\Model\WorkOrder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WorkOrderModel
{
    protected $SSWorkOrderID;

    public function handleWorkOrder(array $workOrderMsg)
    {
        if (empty($this->SSWorkOrderID)) {
            $this->generateSSWorkOrderID();
        }

        $workOrderMsg = filter_var_array($workOrderMsg, FILTER_SANITIZE_STRING, true);
        app('debugbar')->info($workOrderMsg);


        if (in_array(null, $workOrderMsg)) {
            //如果用户有上传附件，则删除
            $filename = '../storage/app/WorkOrder/attachment/' . Session::get('username') . '/' . $this->SSWorkOrderID . '.jpg';
            if (file_exists($filename)) {
                unlink($filename);
            }
            app('debugbar')->info($filename);
            return [
                'type' => 0,
                'msg' => '信息填写不完整'
            ];
        }

        DB::table('wo_record')->insert([
            'username' => Session::get('username'),
            'order_num' => $workOrderMsg['orderNum'],
            'title' => $workOrderMsg['title'],
            'ques_sort' => $workOrderMsg['type'],
            'detail' => $workOrderMsg['detail'],
            'wo_no' => $this->SSWorkOrderID
        ]);
        return [
            'type' => 1,
            'msg' => '提交成功'
        ];
    }

    public function generateSSWorkOrderID()
    {
        return $this->SSWorkOrderID = 'SSWO' . date('YmdHis') . mt_rand(1000, 9999);
    }

    public function handleWorkOrderFile(Request $request)
    {
        $this->generateSSWorkOrderID();
        app('debugbar')->info($request->file('picture')->extension(), $this->SSWorkOrderID);

        if ($request->file('picture')->isValid()) {
            if ($request->file('picture')->extension() == 'jpeg') {
                $request->file('picture')->storeAs('WorkOrder/attachment/' . Session::get('username') . '/', $this->SSWorkOrderID . '.jpg');
                return [
                    'type' => 1,
                    'msg' => '保存成功'
                ];
            } else {
                return [
                    'type' => 0,
                    'msg' => '文件类型不符合，仅支持jpg文件'
                ];
            }
        } else {
            return [
                'type' => 0,
                'msg' => '文件上传不完整'
            ];
        }

    }
}