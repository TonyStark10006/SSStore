<?php

namespace App\Model\Activity\LuckyMoney;

use App\Http\Controllers\publicTool\filterTrait;
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GetLuckyMoneyModel
{
    //获取红包信息
    private $luckyMoneyID;
    private $luckyMoneyDetail;
    private $remainNumber;
    private $remainPeriod;
    public $fetchRecord;
    public $LMMinPeriod;

    //产生的随机红包时长
    private $randomTime;
    private $periodFetched;



    use filterTrait;
    //
    public function __construct($request)
    {
        if ($request->has('LM')) {
            $this->luckyMoneyID = self::filter($request->get('LM'));
            $this->luckyMoneyDetail = DB::table('lm_list')
                ->where('lm_id', $this->luckyMoneyID)
                ->first();

            if (!empty($this->luckyMoneyDetail)) {
                $this->remainNumber = $this->luckyMoneyDetail->remain_number;
                $this->remainPeriod = $this->luckyMoneyDetail->remain_period;
                $this->LMMinPeriod = $this->luckyMoneyDetail->min_period;
            }
        }


    }

    public function minRandomTime($minTime, $remainPeriod)
    {
        //假如业务规则设定有最小值，则需要用这个函数
        $this->randomTime = mt_rand($minTime, $remainPeriod);// - $minTime
        $this->remainPeriod = $this->remainPeriod - $this->randomTime;
        return $this->randomTime;
    }

    private function evaluate()
    {
        //判断红包ID是否有效
        if (empty($this->luckyMoneyDetail)) {
            return array(
                'type' => 1,
                'msg' => '该红包无效'
            );
            //return view('activity.luckyMoney', ['msg' => '该红包无效', 'title' => '获取红包']);
        }

        //判断用户是否曾经领取过
        if (!empty($this->fetchRecord)) {
            if ($this->fetchRecord->fetch_status >= 1) {
                return array(
                    'type' => 2,
                    'msg' => '你已经抢过这个红包啦',
                    'period' => round($this->fetchRecord->period_fetched / 60),
                    'zone' => self::getClientZone(),
                    'LMID' => $this->luckyMoneyID
                );
                /*return view('activity.luckyMoney',
                    [
                        'msg' => '你已经抢过这个红包啦',
                        'period' => round($this->fetchRecord->period_fetched / 60),
                        'zone' => self::getClientZone(),
                        'LMID' => $this->luckyMoneyID
                    ]);*/
            }
        }

        if ($this->remainNumber == 0) {
            return array(
                'type' => 1,
                'msg' => '该红包已经被抢完啦'
            );
            //return view('activity.luckyMoney', ['msg' => '该红包已经被抢完啦']);
        }

        //剩余时长大于最小时长时则不能再领取红包
        if ($this->LMMinPeriod) {
            if ($this->remainPeriod < $this->LMMinPeriod) {
                return array(
                    'type' => 1,
                    'msg' => '该红包已经被抢完啦'
                );
                //return view('activity.luckyMoney', ['msg' => '该红包已经被抢完啦']);
            }
        }

        //默认返回false，表示评估后没有问题
        return false;

    }

    public function getClientZone()
    {
        /*
         * 用户为当前登录用户
         * 查询用户已经开通VPN区域，用于领取红包后续时，返回数组。
         * 注意：如果结果为空返回[]
         * Array ( [0] => 日本 [1] => 香港 )
         *
         */

        return DB::table('usage_status')
                    ->where('user_id', Session::get('user_id'))//'2')
                    ->groupBy('zone_name')
                    ->pluck('zone_name');

        //return $result;
    }

    public function getLuckyMoney()
    {
        //获取用户领用记录信息
        $this->fetchRecord = DB::table('lm_list_fetched')
            ->where([
                'user_id' => Session::get('user_id'),
                'lm_id' => $this->luckyMoneyID
            ])
            ->first();

        //判断红包状态以及用户是否已经抢过红包
        if (self::evaluate()) {
            return self::evaluate();
        }

        //生成红包随机时长
        $this->periodFetched = $this->minRandomTime($this->LMMinPeriod, $this->remainPeriod);

        //开始写库事务
        DB::beginTransaction();

        //下面执行用户获取红包操作,假设红包设定最小时长
        $result = DB::table('lm_list_fetched')
            ->insert([
                'lm_id' => $this->luckyMoneyID,
                'user_id' => Session::get('user_id'),
                'period_fetched' => $this->periodFetched,
                'zone_fetched' => 0,
                'fetch_status' => 1,
            ]);

        //更新红包表，减少相应的红包时间以及红包个数
        $updateResult = DB::table('lm_list')
            ->where('lm_id', $this->luckyMoneyID)
            ->update([
                'remain_period' => $this->remainPeriod,
                'remain_number' => -- $this->remainNumber
            ]);

        if (!$result || !$updateResult) {
            DB::rollBack();
        }

        DB::commit();

        return array(
            'type' => 3,
            'msg' => '抢到红包啦',
            'period' =>  round($this->periodFetched / 60),
            'zone' => self::getClientZone(),
            'LMID' => $this->luckyMoneyID
        );

        /*return view('activity.luckyMoney', [
            'msg' => '抢到红包啦',
            'period' =>  round($this->periodFetched / 60),
            'zone' => self::getClientZone(),
            'LMID' => $this->luckyMoneyID
        ]);*/

    }

}
