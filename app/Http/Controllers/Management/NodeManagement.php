<?php
namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Model\Management\NodeManagementModel;
use Illuminate\Http\Request;

class NodeManagement extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function addANode()
    {
        $model = new NodeManagementModel($this->request);
        return $model->addANode();
    }
}