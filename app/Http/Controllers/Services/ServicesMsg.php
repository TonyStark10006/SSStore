<?php

namespace App\Http\Controllers\Services;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Model\Services\UsageSummary;
use App\Http\Controllers\publicTool\filterTrait;

class ServicesMsg extends Controller
{
    private $request;
    private $type;
    //
    use filterTrait;
    public function __construct(Request $request)
    {
        $this->request = $request;
        if ($request->has('type')) {
            $this->type = $this->filter($request->input('type'));
        }
    }

    public function go()
    {
        if ($this->type == 'sevendaysflow') {
            return self::getSevenDaysFlow();
        }

        if ($this->type == 'threemonthsflow') {
            return self::getThreeMonthsFlow();
        }

        return response('', 404);
    }

    public function getServicesMsg()
    {
        $model = new UsageSummary($this->request);
        return $model->getServicesMsg();
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
