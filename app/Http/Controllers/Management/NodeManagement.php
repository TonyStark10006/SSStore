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

    public function queryNodeList()
    {
        $model = new NodeManagementModel($this->request);
        $nodeList = $model->nodeList();
        //拼凑返回的HTML
        app('debugbar')->info($nodeList);
        /*<select name="queryNodeName" id="queryNodeName" class="form-control">
            <option value="KOR">韩国</option>
            <option value="HK">香港</option>
        </select>*/
        //$result = '<select name="queryNodeName" id="queryNodeName" class="form-control">';
        $result = '';
        foreach ($nodeList as $item) {
            $result .= '<option value="' . $item->zone_id . '">' . $item->zone_name . '</option>';
        }
        return $result;// .= '</select>';
    }

    public function queryNodeMsg()
    {
        $model = new NodeManagementModel($this->request);
        $nodeMsg = $model->nodeMsg();
        app('debugbar')->info($nodeMsg);
        return $nodeMsg;
    }

    public function updateNodeMsg()
    {
        $model = new NodeManagementModel($this->request);
        return $model->modifyANode();
    }
}