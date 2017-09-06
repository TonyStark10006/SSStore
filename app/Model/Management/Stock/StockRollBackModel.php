<?php
namespace App\Model\Management\Stock;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StockRollBackModel extends StockModel
{
    private $orderArray;

    public function __construct(array $orderArray)
    {
        //传入一维数组
        $this->orderArray = $orderArray;
    }

    public function stockRollBack()
    {
        $sql1 = 'SELECT zone_id, zone_name, period, remain_period, order_no FROM `stock_log` WHERE ';
        foreach ($this->orderArray as $orderNO) {
            $sql1 .= '`order_no` = ? ' . 'OR ';
        }
        //去掉SQL语句最后的‘ OR ’
        $sql = substr($sql1, 0, -4);
        //查询所有订单的库存记录，获得订单的节点ID、名称，购买月数，剩余月数，订单号
        $stockLogResult = DB::select($sql, $this->orderArray);
        $affectedRows = 0;
        //历遍库存扣减记录，更新库存表剩余库存为 当前库存+订单扣减库存 ，从而实现库存还原
        foreach ($stockLogResult as $key => $orderDetail) {
            $affectedRows += DB::update('UPDATE `stock` SET `remain_period` = ? + `remain_period`  WHERE `zone_name` = ?',
                [$orderDetail->period, $orderDetail->zone_name]);
            DB::table('stock_log')->insert([
                'zone_id' => $orderDetail->zone_id,
                'zone_name' => $orderDetail->zone_name,
                'period' => $orderDetail->period,
                'remain_period' => 0,
                'buyer' => Session::get('user_id'),
                'order_no' => $orderDetail->order_no . '取消',
                'remark' => '订单取消，库存还原。操作人：' .  Session::get('user_id')
            ]);
        }
        return $affectedRows;
    }
}