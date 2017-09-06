<?php
namespace App\Model\Order;

use App\Model\Management\Stock\StockRollBackModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $sql1 = 'UPDATE `order` SET `pay_status` = 2 WHERE ';
        foreach ($this->data as $orderNO) {
            //$sql1 .= '\'' . $orderNO . '\' ' . 'OR';
            $sql1 .= '`order_no` = ? ' . 'OR ';
        }
        //去掉SQL语句最后的‘ OR ’
        $sql = substr($sql1, 0, -4);
        //更新订单的支付状态 0=>未充值 1=>已充值 2=>取消订单
        $affectedRows = DB::update($sql, $this->data);

        //return $affectedRows;
        //库存回滚，返回更新行数
        $rollBack = new StockRollBackModel($this->data);
        $rollBackRows = $rollBack->stockRollBack();
        if ($rollBackRows && $affectedRows) {
            return '取消成功';
        } else {
            return '取消失败或者无操作';
        }
        //return $this->data;
    }
}