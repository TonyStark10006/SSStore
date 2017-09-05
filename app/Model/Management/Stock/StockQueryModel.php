<?php
namespace App\Model\Management\Stock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockQueryModel extends StockModel
{
    private $zoneNameQuery;
    private $zoneNameLogQuery;
    private $date3;
    private $date4;

    private $result;

    public function __construct(Request $request)
    {
        if ($request->has('zoneNameQuery')) {
            $this->zoneNameQuery = $this->filter($request->input('zoneNameQuery'));
        }

        if ($request->has('date3')) {
            $this->date3 = $this->filter($request->input('date3'));
            $this->date4 = $this->filter($request->input('date4'));
            $this->zoneNameLogQuery = $this->filter($request->input('zoneNameLogQuery'));
        }


    }

    public function remainQuery()
    {
        //return dd(DB::table('stock')->get());
        if (empty($this->zoneNameQuery)) {
            $result = DB::table('stock')->get();
        } else {
            $result = DB::table('stock')->where('zone_name', $this->zoneNameQuery)->get();
        }

        //生成表格
        $this->result =  '<br><table class="table table-hover">
        <thead>
        <tr>
            <th>节点ID</th>
            <th>节点名称</th>
            <th>剩余时长</th>
            <th>备注</th>
            <th>创建时间</th>
        </tr>
        </thead>
        <tbody>';

        foreach ($result as $items) {
            $this->result .= '<tr><td>';//<th>选择</th><input type="checkbox" value="' . $items->order_no . '"/></td><td>
            $this->result .= $items->zone_id . '</td><td>';
            $this->result .= $items->zone_name . '</td><td>';
            $this->result .= $items->remain_period . '个月' . '</td><td>';
            $this->result .= $items->remark . '</td><td>';
            $this->result .= $items->update_time .'</td></tr>';
        }

        $this->result .= '</tbody></table>';

        return $this->result;

    }

    public function stockLogQuery()
    {
        /*
         * Illuminate\Support\Collection Object (
         * [items:protected] => Array (
         * [0] => stdClass Object ( [id] => 1 [zone_id] => 1 [zone_name] => 日本 [period] => 20 [remain_period] => 3 [order_no] => [buyer] => 测试1 [create_time] => 2017-08-29 17:07:30 [remark] => 新增库存 )
         * [1] => stdClass Object ( [id] => 2 [zone_id] => [zone_name] => 日本 [period] => 1 [remain_period] => [order_no] => [buyer] => 1 [create_time] => 2017-08-29 16:17:08 [remark] => )
         * [3] => stdClass Object ( [id] => 4 [zone_id] => [zone_name] => 日本 [period] => 6 [remain_period] => [order_no] => [buyer] => 1 [create_time] => 2017-08-29 16:32:53 [remark] => )
         * [11] => stdClass Object ( [id] => 12 [zone_id] => 2 [zone_name] => 韩国 [period] => 100 [remain_period] => 103 [order_no] => [buyer] => 1 [create_time] => 2017-08-30 18:27:46 [remark] => 新增库存 ) ) )
         * */
        if (empty($this->zoneNameLogQuery)) {
            $result =  DB::table('stock_log')
                ->whereBetween('create_time', [$this->date3, $this->date4])
                ->get();
        } else {
            $result =  DB::table('stock_log')
                ->where('zone_name', $this->zoneNameLogQuery)
                ->whereBetween('create_time', [$this->date3, $this->date4])
                ->get();
        }

        $this->result =  '<br><table class="table table-hover">
        <thead>
        <tr>
            <th>节点ID</th>
            <th>节点名称</th>
            <th>时长</th>
            <th>剩余时长</th>
            <th>订单号</th>
            <th>购买者/操作人</th>
            <th>备注</th>
            <th>创建时间</th>
        </tr>
        </thead>
        <tbody>';

        //历遍查询结果，嵌套到表格中返回
        foreach ($result as $key => $items){
            $this->result .= '<tr><td>';//<th>选择</th><input type="checkbox" value="' . $items->order_no . '"/></td><td>
            $this->result .= $items->zone_id . '</td><td>';
            $this->result .= $items->zone_name . '</td><td>';
            $this->result .= $items->period . '个月' . '</td><td>';
            $this->result .= $items->remain_period . '个月' . '</td><td>';
            $this->result .= $items->order_no . '</td><td>';
            $this->result .= $items->buyer . '</td><td>';
            $this->result .= $items->remark . '</td><td>';
            $this->result .= $items->create_time .'</td></tr>';
        }

        $this->result .= '</tbody></table>';

        return $this->result;
    }

    public function realTimeStockQuery()
    {
        /*
         * Illuminate\Support\Collection Object ( [items:protected] => Array ( [0] => stdClass Object ( [id] => 1 [zone_id] => 2 [zone_name] => 韩国 [remain_period] => 105 [remark] => 2017年08月31日 16:12:44 [update_time] => 2017-08-31 16:12:44 )
         * [1] => stdClass Object ( [id] => 2 [zone_id] => 1 [zone_name] => 日本 [remain_period] => 100 [remark] => 新增库存 [update_time] => 2017-08-31 10:56:18 )
         * [2] => stdClass Object ( [id] => 4 [zone_id] => 3 [zone_name] => 香港 [remain_period] => 52 [remark] => 调增2个月 [update_time] => 2017-08-31 16:27:39 ) ) )*/
        $result = DB::table('stock')->get();
        $script = '';
        foreach ($result as $items) {
            $script .= "case '{$items->zone_name}': stock={$items->remain_period};break;";
        }
        $response = <<<JS
            var zone = $("#zone").val();
            var stock;
            switch(zone) {
            {$script}
            }
        
            $("#stock").text(stock);
            
            $("#zone").change(function() {
                var zone = $("#zone").val();
                var stock;
                switch(zone) {
                {$script}
                }
                
                $("#stock").text(stock);
            });
JS;
            /*"
            var zone = $(\"#zone\").val();
            var stock;
            switch(zone) {
            {$script}
            }
        
            $(\"#stock\").text(stock);
            
            $(\"#zone\").change(function() {
                var zone = $(\"#zone\").val();
                var stock;
                switch(zone) {
                {$script}
                }
                
                $(\"#stock\").text(stock);
            });
"*/;
        return response($response, 200)
            ->header('Content-Type', 'application/x-javascript')
            ->header('Cache-Control', 'no-store');

    }
}