<?php

namespace App\Http\Controllers\Int;

use App\Http\Controllers\publicTool\filterTrait;
use App\Model\Management\Stock\StockQueryModel;
use App\Model\Services\UsageSummary;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Resource extends Controller
{
    //
    private $request;
    private $type;


    use filterTrait;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->type = $this->filter($request->input('type'));
    }

    public function go()
    {
        if ($this->type == 'orderstock') {
            return self::getIRealTimeStock();
        }

        if ($this->type == 'sevendaysflow') {
            return self::getSevenDaysFlow();
        }

        if ($this->type == 'threemonthsflow') {
            return self::getThreeMonthsFlow();
        }

        return response('', 404);
    }

    public function getIRealTimeStock()
    {
        $model = new StockQueryModel($this->request);
        return $model->realTimeStockQuery();
    }

    public function getSevenDaysFlow()
    {
        $model = new UsageSummary($this->request);
        return $model->getSevenDaysFlow();
    }

    public function getThreeMonthsFlow()
    {
        $model = new UsageSummary($this->request);
        return $model->getThreeMonthsFlow();
    }
}
