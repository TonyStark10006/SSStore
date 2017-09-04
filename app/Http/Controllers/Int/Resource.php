<?php

namespace App\Http\Controllers\Int;

use App\Http\Controllers\publicTool\filterTrait;
use App\Model\Management\Stock\StockQueryModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        return false;
    }

    public function getIRealTimeStock()
    {
        $model = new StockQueryModel($this->request);
        return $model->realTimeStockQuery();
    }
}
