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

}
