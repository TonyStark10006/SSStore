<?php
namespace App\Http\Order\HandleOrder;

use App\Model\DB\DBInsert\DBInsert;
use App\Model\DB\DBQuery\DBQuery;
use App\Traits\Observable\Observable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HandleOrder
{
    private $originOrderMsg;
    public $DBQuery;
    public $DBInsert;

    private $orderNO;

    private $zone;
    private $period;
    private $totalPrice;
    private $orderType;
    private $password;
    private $couponCode;

    use Observable;

    public function __construct(Request $request, DBQuery $query, DBInsert $insert)
    {
        $this->originOrderMsg = filter_var_array($request->all(), FILTER_SANITIZE_STRING);
        $this->DBQuery = $query;
        $this->DBInsert = $insert;
    }

    public function handleOrder()
    {
        //查询节点信息
        //$nodeListMsg = $this->db->getAllWithAKey('node_list', 'zone_id', $this->originOrderMsg['zone']);
        $nodeListMsg = get_object_vars($this->DBQuery
            ->getSelectedWithAKey('node_list', 'zone_id', $this->originOrderMsg['zone'], '\'zone_name\', \'price\''));

        //获取当前用户购买节点的剩余时长
        $expiration = $this->DBQuery->getAValueWithConditions(
                'usage_status',
                [
                'user_id' => Session::get('user_id'),
                'zone_name' => $this->originOrderMsg['zone']
                ],
                'valid_time'
            );

        //生成订单号
        $this->orderNO = 'SS' . date('YmdHis') . mt_rand(1000, 9999);

        //处理节点新订单，如果已经有目标节点的使用记录，则返回提示
        if ($this->originOrderMsg['orderType'] == 1) {

            if (empty($expiration)) {

                if (!$this->originOrderMsg['zone']
                    || !$this->originOrderMsg['period']
                    || !$this->originOrderMsg['totalPrice']
                    || !$this->originOrderMsg['password']
                ) {
                    return redirect('/')->with('message', '1');
                }

                $this->DBInsert->insertANArray(
                    'order',
                    [
                        'user_id' => Session::get('user_id'),
                        'order_no' => $this->orderNO,//'SS' . date('YmdHis') . mt_rand(1000, 9999),
                        'zone_id' => $nodeListMsg['zone_name'],
                        'zone_name' => $this->originOrderMsg['zone'],
                        'period' => $this->originOrderMsg['period'],
                        'total_price' => $this->originOrderMsg['totalPrice'],
                        'password' => $this->originOrderMsg['password'],
                        'order_type' => $this->originOrderMsg['orderType'],
                        'pay_status' => 0
                    ]);

                return redirect('/')->with('message', '4');
            } else {
                return redirect('/')->with('message', '5');//'您已经购买过该节点的VPN，请续费';
            }

            //处理续费订单
        } elseif ($this->originOrderMsg['orderType'] == '2') {

            if ($expiration) {
                if (!$this->originOrderMsg['zone'] || !$this->originOrderMsg['period'] || !$this->originOrderMsg['totalPrice']) {
                    return redirect('/')->with('message', '1');
                }

                //续费订单没有password数据
                $this->DBInsert->insertANArray(
                    'order',
                    [
                        'user_id' => Session::get('user_id'),
                        'order_no' => $this->orderNO,//'SS' . date('YmdHis') . mt_rand(1000, 9999),
                        'zone_id' => $nodeListMsg['zone_name'],
                        'zone_name' => $this->originOrderMsg['zone'],
                        'period' => $this->originOrderMsg['period'],
                        'total_price' => $this->originOrderMsg['totalPrice'],
                        'order_type' => $this->originOrderMsg['orderType'],
                        'pay_status' => 0
                    ]);

                //发送订单确认邮件
                /*$this->userMsg = DB::table('member')
                    ->select('email','username')
                    ->where('user_id', Session::get('user_id'))
                    ->get();*/
                /*Mail::to($this->userMsg[0]->email)
                    ->send(
                        new OrderMsg(
                            $this->userMsg[0]->username,
                            $this->orderNO,
                            $this->period,
                            $this->orderType,
                            $this->zone
                        )
                    );*/

                //增加发送邮件任务到队列
                /*dispatch(new SendEmail(
                    $this->userMsg[0]->email,
                    array(
                        1,//邮件类型，下单成功通知邮件类型为1
                        $this->userMsg[0]->username,
                        $this->orderNO,
                        $this->period,
                        $this->orderType,
                        $this->zone
                        )
                    ));*/

                return redirect('/')->with('message', '4');
            } else {
                return redirect('/')->with('message', '6');
            }
        } else {
            return redirect('404');
        }

    }

    public function verifyOrderDetail($orderArray, $priceList)
    {
        //匹配地区值，中文字符
        $this->zone = preg_match("/[\x{4e00}-\x{9fa5}]+/u", $orderArray['zone']) ? $orderArray['zone'] : false;

        //匹配时长字段，一位数字，值为1到6
        $this->period = preg_match("/^[1-6]$/", $orderArray['period']) ? $orderArray['period'] : false;

        //过滤订单类型
        $this->orderType = filter_var($orderArray['orderType'], FILTER_VALIDATE_INT) ? $orderArray['orderType'] : false;

        //验证优惠码格式
        $this->couponCode = filter_var($orderArray['couponCode'], FILTER_VALIDATE_EMAIL) ? $orderArray['couponCode'] : false;

        //匹配SS登录密码，数字字母自由组合，6到20位
        $this->password = preg_match("/^(\w){6,20}$/",$orderArray['password']) && strlen($orderArray['password']) <= 20 ?  $orderArray['password'] : false;

        //计算价格
        if (!empty($this->period)) {
            $this->totalPrice = $priceList['price'] * $this->period;
        } else {
            $this->totalPrice = false;
        }

        //第一次购买的订单
        if ($this->orderType == 1) {
            return array(
                'zone' => $this->zone,
                'period' => $this->period,
                'totalPrice' => $this->totalPrice,
                'orderType' => $this->orderType,
                'password' => $this->password,
                //'couponCode' => $this->couponCode
            );
        //续费订单
        } elseif ($this->orderType == 2) {
            return array(
                'zone' => $this->zone,
                'period' => $this->period,
                'totalPrice' => $this->totalPrice,
                'orderType' => $this->orderType
                //'couponCode' => $this->couponCode
            );
        } else {
            return false;
        }
    }

}