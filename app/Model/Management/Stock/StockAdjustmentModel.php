<?php
namespace App\Model\Management\Stock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentModel extends StockModel
{
    //
    private $action;
    private $zoneID;
    private $zoneName;
    private $period;
    private $remark;
    private $remainPeriod;

    public function __construct(Request $request)
    {
        $this->action = filter_var($request->input('action'), FILTER_SANITIZE_NUMBER_INT);
        $this->zoneName = $this->filter($request->input('zoneNameManagement'));
        $this->period = filter_var($request->input('period'), FILTER_SANITIZE_NUMBER_INT);
        $this->remark = $this->filter($request->input('remark'));
        $this->zoneID = DB::table('node_list')->where('zone_name', $this->zoneName)->value('zone_id');
    }

    public function adjustStock()
    {
        if (empty($this->zoneID)) {
            return '节点不存在';
        }

        //查询目标节点的剩余库存
        $this->remainPeriod = DB::table('stock')->where('zone_name', $this->zoneName)->value('remain_period');

        //新增节点后进行全新入库操作
        if ($this->action == '1') {

            if (empty($this->remainPeriod)) {

                $result = DB::table('stock')
                    ->where('zone_name', $this->zoneName)
                    ->insert([
                        'zone_id' => $this->zoneID,
                        'zone_name' => $this->zoneName,
                        'remain_period' => $this->period,
                        'remark' => date('Y年m月d日 H:i:s入库') . $this->remark
                    ]);

                if ($result) {
                    return '入库成功，' . $this->zoneName . '节点库存时长增加' . $this->period . '月';
                } else {
                    return '入库出错请重新操作';
                }
            } else {
                return '该节点已有库存，不能进行全新入库操作';
            }
        }

        if ($this->action == '2') {
            $result = DB::table('stock')
                ->where('zone_name', $this->zoneName)
                ->update([
                    //'zone_id' => $this->zoneID,
                    //'zone_name' => $this->zoneName,
                    'remain_period' => $this->period + $this->remainPeriod,
                    'remark' => $this->remark
                ]);

            if ($result) {
                return '节点：' . $this->zoneName . ' 库存时长增加' . $this->period . '个月成功';
            } else {
                return '请检查节点名输入是否正确';
            }
        }

        if ($this->action == '3') {
            $result = DB::table('stock')
                ->where('zone_name', $this->zoneName)
                ->update([
                    //'zone_id' => $this->zoneID,
                    //'zone_name' => $this->zoneName,
                    'remain_period' => $this->remainPeriod - $this->period,
                    'remark' => $this->remark
                ]);

            if ($result) {
                return '节点：' . $this->zoneName . ' 库存时长减少' . $this->period . '个月成功';
            } else {
                return '请检查节点名输入是否正确';
            }
        }

        return '动作无效';

    }
}