<?php
namespace App\Model\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\publicTool\filterTrait;

class UsageSummary
{
    private $nodeID;
    public $timestamp;
    private $servicesMsg;


    use filterTrait;

    public function __construct($request)
    {
        if ($request->has('node_id')) {
            $this->nodeID = $this->filter($request->input('node_id'));
            $this->timestamp = time();
        }
    }


    public function getServicesMsg()
    {
        $this->servicesMsg = DB::table('user')
            ->leftJoin('node_list', 'user.node_id', '=', 'node_list.id')
            ->select('user.node_name', 'user.node_id', 'user.user_name', 'user.uid', 'user.passwd', 'user.t', 'user.u', 'user.d',
                'user.transfer_enable', 'user.port', 'user.protocol', 'user.obfs', 'user.enable', 'user.expire_time',
                'user.method', 'node_list.server')
            ->where('user.uid', Session::get('user_id'))
            ->get();
        //app('debugbar')->info($this->servicesMsg);
        return view('servicesMsg', [
            'Msg' => $this->servicesMsg
        ]);
    }

    public function getSevenDaysFlow()
    {
        $beginning = strtotime(date("Y-m-d"));

        /*//planA
        //计算今天流量
        $flow[6] = DB::table('user')
            //->select('user_traffic_log.traffic', 'user_traffic_log.user_id', 'user_traffic_log.u', 'user_traffic_log.d',
            //    'user_traffic_log.log_time', 'user.uid', 'user.node_name' )
            ->leftJoin('user_traffic_log', 'user_traffic_log.user_id', '=', 'user.id')
            ->where(['user.uid' => Session::get('user_id'), 'user_traffic_log.node_id' => $this->nodeID])
            ->whereBetween('user_traffic_log.log_time', [$beginning, $this->timestamp])
            ->sum('user_traffic_log.traffic');


        //查询并计算前6天流量
        for ($i = 5, $roll = $beginning; $i >= 0; $i--) {
            $flow[$i] = DB::table('user')
                ->leftJoin('user_traffic_log', 'user_traffic_log.user_id', '=', 'user.id')
                ->where(['user.uid' => Session::get('user_id'), 'user_traffic_log.node_id' => $this->nodeID])
                ->whereBetween('user_traffic_log.log_time', [strtotime("-1 days", $roll), $roll])
                ->sum('user_traffic_log.traffic');
            $roll = strtotime("-1 days", $roll);
        }*/

        //planB
        //查询七天所有流量
        $result2 = DB::table('user')
            ->leftJoin('user_traffic_log', 'user_traffic_log.user_id', '=', 'user.id')
            ->where(['user.uid' => Session::get('user_id'), 'user_traffic_log.node_id' => $this->nodeID])
            ->whereBetween('user_traffic_log.log_time', [strtotime("-7 days", $beginning), time()])
            ->select('user_traffic_log.traffic', 'user_traffic_log.log_time', 'user_traffic_log.user_id', 'user_traffic_log.node_id')
            ->get();

        //根据每一天的起始结束时间戳历遍查询结果数组的流量信息，并且要从数组中剔除已经历遍的记录
        $flow = array(0, 0, 0, 0, 0, 0, 0);
        //今天流量使用记录
        foreach ($result2 as $key => $items) {
            //app('debugbar')->info($items->traffic);;
            if ($items->log_time >= $beginning) {
                $flow[6] += $items->traffic;
            } else {
                for ($i = 5, $roll = $beginning;$i >= 0; $i--) {
                    if ($items->log_time >= strtotime("-1 days", $roll) && $items->log_time < $roll) {
                        $flow[$i] += $items->traffic;
                        break;//save 1/3 time
                    }
                    $roll = strtotime("-1 days", $roll);
                }
            }
            //unset($result2[$key]);
        }
        //给数组排序，返回true
        ksort($flow);
        //app('debugbar')->info(json_encode($flow, JSON_NUMERIC_CHECK), $flow, $beginning);
        return $flow;
    }

    public function getThreeMonthsFlow()
    {
        $beginning = strtotime(date('Y-m'));
        //计算本月流量
        $Flow[2] = DB::table('user')
            ->leftJoin('user_traffic_log', 'user_traffic_log.user_id', '=', 'user.id')
            ->where(['user.uid' => Session::get('user_id'), 'user_traffic_log.node_id' => $this->nodeID])
            ->whereBetween('user_traffic_log.log_time', [$beginning, $this->timestamp])
            ->sum('user_traffic_log.traffic');

        //查询并计算前2个月流量
        for ($i = 1, $roll = $beginning; $i >= 0; $i--) {
            $Flow[$i] = DB::table('user')
                ->leftJoin('user_traffic_log', 'user_traffic_log.user_id', '=', 'user.id')
                ->where(['user.uid' => Session::get('user_id'), 'user_traffic_log.node_id' => $this->nodeID])
                ->whereBetween('user_traffic_log.log_time', [strtotime("-1 months", $roll), $roll])
                ->sum('user_traffic_log.traffic');
            $roll = strtotime("-1 months", $roll);
        }

        app('debugbar')->info($beginning);
        //给数组排序，返回true
        ksort($Flow);
        return $Flow;
    }
}