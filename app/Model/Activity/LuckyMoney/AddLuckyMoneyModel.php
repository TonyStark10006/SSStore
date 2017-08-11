<?php
namespace App\Model\Activity\LuckyMoney;

use App\Http\Controllers\publicTool\filterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddLuckyMoneyModel
{
    //
    //新增红包
    protected $action;
    protected $luckyMoneyNumber;
    protected $luckyMoneyPeriod;
    protected $minPeriod;

    use filterTrait;


    public function __construct($request)
    {
        //新增一个红包
        if ($request->has('action')
            && $request->has('number')
            && $request->has('period')
            || $request->has('minPeriod')
        ) {
            $this->action = self::filter($request->input('action'));
            $this->luckyMoneyNumber = self::filter($request->input('number'));
            $this->luckyMoneyPeriod = self::filter($request->input('period')) * 60;
            $this->minPeriod = self::filter($request->input('minPeriod')) * 60;
        }
    }

    //新增红包
    public function addLuckyMoney()
    {
        if ($this->action !== '1') {
            return '不是新增红包';
        }

        if (!empty($this->minPeriod)) {
            if ($this->minPeriod * $this->luckyMoneyNumber > $this->luckyMoneyPeriod) {
                return '最小时长乘以数量大于总时长';
            }
        }

        $lm_id = 'LM' . date('YmdHis') . mt_rand(1000, 9999);

        $result = DB::table('lm_list')->insert([
            'lm_id' => $lm_id,
            'total_period' => $this->luckyMoneyPeriod,
            'remain_period' => $this->luckyMoneyPeriod,
            'min_period' => $this->minPeriod,
            'total_number' => $this->luckyMoneyNumber,
            'remain_number' => $this->luckyMoneyNumber
        ]);

        if ($result) {
            return '创建红包成功，红包ID为' . $lm_id . '，分享链接为'. url('getLuckyMoney') . '?LM=' . $lm_id;

        } else {
            return '创建失败';
        }

    }

}