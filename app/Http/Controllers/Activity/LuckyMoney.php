<?php

namespace App\Http\Controllers\Activity;

use App\Model\Activity\LuckyMoney\AddLuckyMoneyModel;
use App\Model\Activity\LuckyMoney\ChargeLuckyMoneyModel;
use App\Model\Activity\LuckyMoney\GetLuckyMoneyModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LuckyMoney extends Controller
{
    //
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getLuckyMoney()
    {
        $getLuckyMoney = new GetLuckyMoneyModel($this->request);
        $result = $getLuckyMoney->getLuckyMoney();

        if ($result['type'] == 2 || $result['type'] == 3) {
            return view('activity.luckyMoney', [
                'msg' => $result['msg'],
                'period' =>  $result['period'],
                'zone' => $result['zone'],
                'LMID' => $result['LMID']
            ]);
        } else {
            return view('activity.luckyMoney', $result['msg']);
        }
    }

    public function addLuckyMoney()
    {
        $addLuckyMoney = new AddLuckyMoneyModel($this->request);
        return $addLuckyMoney->addLuckyMoney();
    }

    public function chargeLuckyMoney()
    {
        $chargeLuckyMoney = new ChargeLuckyMoneyModel($this->request);
        return $chargeLuckyMoney->chargeLuckyMoney();
    }
}
