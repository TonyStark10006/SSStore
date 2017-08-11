<?php
namespace App\Model\Activity\LuckyMoney;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ChargeLuckyMoneyModel
{
    protected $userID;
    protected $targetZone;
    protected $lm_id;
    protected $lm_fetched;
    protected $period_fetch;
    protected $period_parent;



    use filterTrait;

    public function __construct($request)
    {
        //获取用户提交的区域以及红包ID
        $this->targetZone = $this->filter(urldecode($request->input('zone')));
        $this->lm_id = $this->filter($request->input('lmID'));

        //获取用户红包获取信息
        $this->lm_fetched = DB::table('lm_list_fetched')
            ->where([
                'user_id' => $this->userID,
                'lm_id' => $this->lm_id
            ])
            ->first();

        //获取用户得到红包时长
        $this->period_fetch = $this->lm_fetched->period_fetched;
    }


    public function chargeLuckyMoney()
    {
        $this->userID = Session::get('user_id');

        if ($this->targetZone == '' || $this->lm_id == '') {
            return '充值地区不能为空';
        }


        //判断用户是否曾经获取过
        if ($this->lm_fetched->fetch_status == 2) {
            return '你已经充值过这个红包啦';
        }


        //获取用户当前时长value('valid_time')
        $this->period_parent = DB::table('usage_status')
            ->where([
                'user_id' => $this->userID,
                'zone_name' => $this->targetZone
            ])
            ->value('valid_time');

        //下面增加用户的使用时长以及修改红包获取状态
        $result_add = DB::table('usage_status')
            ->where([
                'user_id' => $this->userID,
                'zone_name' => $this->targetZone
            ])
            ->update([
                'valid_time' => $this->period_parent + $this->period_fetch
            ]);

        $result_change = DB::table('lm_list_fetched')
            ->where([
                'user_id' => $this->userID,
                'lm_id' => $this->lm_id
            ])
            ->update([
                'fetch_status' => 2,
                'zone_fetched' => $this->targetZone
            ]);

        if ($result_add && $result_change) {
            return "充值成功啦";
        } else {
            return "充值失败";
        }



    }
}