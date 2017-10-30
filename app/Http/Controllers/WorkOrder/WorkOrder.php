<?php

namespace App\Http\Controllers\WorkOrder;

use App\Model\WorkOrder\WorkOrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkOrder extends Controller
{

    public function handleWorkOrder(Request $request, WorkOrderModel $WorkOrderModel)
    {
        //判断上传图片
        if ($request->hasFile('picture')) {
            $result = $WorkOrderModel->handleWorkOrderFile($request);
            if ($result['type'] == 0) {
                return redirect('workorder')
                    ->with([
                        'msg' => $result['msg'],
                        'type' => $result['type']
                    ]);
            }
        }

        //没有文件或者文件校验通过则开始校验工单信息
        $result1 = $WorkOrderModel->handleWorkOrder($request->all());
        return redirect('workorder')
            ->with([
                'msg' => $result1['msg'],
                'type' => $result1['type']
            ]);
    }

    public function queryWorkOrderRecord(Request $request, WorkOrderModel $workOrderModel) {
        $data = filter_var_array($request->all(), FILTER_SANITIZE_STRING, true);
        app('debugbar')->info($data);
        $result = $workOrderModel->queryWorkOrderRecord($data['date1'], $data['date2']);
        $result1 = '<br><table class="table table-hover">
            <thead>
            <tr>
                <th>工单号</th>
                <th>标题</th>
                <th>类别</th>
                <th>详细内容</th>
                <th>订单号</th>
                <th>提交时间</th>
                <th>附件</th>
                <th>处理状态</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($result as $key => $items){
            $result1 .= '<tr><td>';
            $result1 .= $items->wo_no . '</td><td>';
            $result1 .= $items->title . '</td><td>';
            $result1 .= $items->ques_sort . '</td><td>';
            $result1 .= $items->detail . '</td><td>';
            $result1 .= $items->order_num . '</td><td>';
            $result1 .= $items->sub_time . '</td><td>';
            $result1 .= '<a href="' . url('resource') . '?type=getWOAttachment&id=' .
                $items->wo_no .'" target="_blank">查看</a></td><td>';
            $result1 .= $items->status;
            $result1 .= '</td></tr>';
        }
        $result1 .= '</tbody>
        </table>';

        return $result1;
    }

}
