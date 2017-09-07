<?php
namespace App\Model\Order;

use App\Model\Management\Stock\StockRollBackModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UpdateOrderStatus extends Order
{
    private $data;

    public function __construct($request)
    {
        //["SS201708291623536286","SS201708291632532576"]
        $this->data = json_decode(
            filter_var(
                $request->input('data'),FILTER_SANITIZE_URL
            )
        );
    }

    public function userCancelOrder()
    {
        if (empty($this->data)) {
            return '请勾选订单进行操作';
        }

        //判断当前订单状态，若是已充值或者已取消则拒绝取消订单请求
        $sqlForPayStatus = 'SELECT `order_no`, `pay_status`, `user_id` FROM `order` WHERE ';
        foreach (self::generator(count($this->data), '`order_no` = ? ' . 'OR ') as $items) {
            $sqlForPayStatus .= $items;
        }
        $sqlForPayStatus = substr($sqlForPayStatus, 0, -4);
        $payStatusResult = DB::select($sqlForPayStatus, $this->data);
        $logInID = Session::get('user_id');
        $permission = Session::get('permission');
        foreach ($payStatusResult as $items1) {
            //app('debugbar')->info($items1->user_id, $logInID, $permission);
            //判断请求取消订单是否是当前用户的订单
            if ($items1->user_id !== $logInID && $permission !== 1) {
                return '你无权取消该订单' . $items1->order_no;
            } elseif ($items1->pay_status == 1 || $items1->pay_status == 2) {
                return '订单' . $items1->order_no . '已充值或者已经取消，无法取消';
            }
        }

        //更新订单的支付状态 0=>未充值 1=>已充值 2=>订单取消
        /*foreach ($this->data as $orderNO) {
            //$sql1 .= '\'' . $orderNO . '\' ' . 'OR';
            $sql1 .= '`order_no` = ? ' . 'OR ';
        }*/
        $sqlForUpdateStatus = 'UPDATE `order` SET `pay_status` = 2 WHERE ';
        foreach (self::generator(count($this->data), '`order_no` = ? ' . 'OR ') as $items2) {
            $sqlForUpdateStatus .= $items2;
        }
        //去掉SQL语句最后的' OR '
        $sqlForUpdateStatus = substr($sqlForUpdateStatus, 0, -4);
        $affectedRows = DB::update($sqlForUpdateStatus, $this->data);
        //return $affectedRows;

        //库存回滚，返回更新行数
        $rollBack = new StockRollBackModel($this->data);
        $rollBackRows = $rollBack->stockRollBack();
        if ($rollBackRows && $affectedRows) {
            return '取消成功';
        } else {
            return '取消失败';
        }
        //return $this->data;
    }

    public function generator($num, $string)
    {
        for ($i = 0; $i < $num; $i++) {
            yield $string;
        }
    }
}