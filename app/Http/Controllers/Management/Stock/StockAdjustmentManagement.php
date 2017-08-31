<?php
namespace App\Http\Controllers\Management\Stock;

use App\Model\Management\Stock\StockAdjustmentModel;
use Illuminate\Http\Request;

class StockAdjustmentManagement extends Stock
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function stockAdjustment()
    {
        $model = new StockAdjustmentModel($this->request);
        return $model->adjustStock();
    }
}