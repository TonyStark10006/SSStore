<?php

namespace App\Http\Controllers\Management\Stock;

use App\Model\Management\Stock\StockQueryModel;
use Illuminate\Http\Request;

class StockQuery extends Stock
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function stockQuery()
    {
        $model = new StockQueryModel($this->request);
        return $model->remainQuery();
    }

    public function stockLogQuery()
    {
        $model = new StockQueryModel($this->request);
        return $model->stockLogQuery();
    }
}