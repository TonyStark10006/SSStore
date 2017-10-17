<?php
namespace App\Model\Management;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NodeManagementModel
{
    private $nodeMsg;

    public function __construct(Request $request)
    {
        /*array:9 [
            "nodeNameGo" => "1"
            "nodeAddress" => "1"
            "encryptMethod" => "aes-256-cfb"
            "protocol" => "origin"
            "obfuscation" => "plain"
            "status" => "ok"
            "chineseNodeName" => "1"
            "description" => "1"
            "price" => "1"
            ]*/
        //$this->request = $request->all();
        $this->nodeMsg = filter_var_array($request->all(), FILTER_SANITIZE_STRING, true);
    }

    public function addANode()
    {
        app('debugbar')->info($this->nodeMsg);
        //判断节点信息是否有空的元素
        if (in_array('', $this->nodeMsg) || in_array(null, $this->nodeMsg)) {
            return '添加节点失败，请检查输入信息';
        }

        //写入节点信息
        $result  = DB::table('node_list')->insert([
            'zone_name' => $this->nodeMsg['chineseNodeName'],
            'price' => $this->nodeMsg['price'],
            'name' => $this->nodeMsg['nodeNameGo'],
            'server' => $this->nodeMsg['nodeAddress'],
            'method' => $this->nodeMsg['encryptMethod'],
            'status' => $this->nodeMsg['status'],
            'description' => $this->nodeMsg['description'],
            'protocol' => $this->nodeMsg['protocol'],
            'obfuscation' => $this->nodeMsg['obfuscation'],
        ]);

        if ($result) {
            return '添加成功';
        } else {
            return '添加失败';
        }
    }

    public function nodeList()
    {
        return DB::table('node_list')->select('zone_name', 'name', 'zone_id')->get();
    }


    public function nodeMsg()
    {
        return DB::table('node_list')->where('zone_id', $this->nodeMsg['queryNodeID'])->get();
    }

    public function modifyANode()
    {
        app('debugbar')->info($this->nodeMsg);
        //判断节点信息是否有空的元素
        if (in_array('', $this->nodeMsg) || in_array(null, $this->nodeMsg)) {
            return '修改节点信息失败，请检查输入信息';
        }

        //更新节点信息
        /*$result  = DB::table('node_list')->update([
            'zone_name' => $this->nodeMsg['modifyChineseNodeName'],
            'price' => $this->nodeMsg['modifyPrice'],
            'name' => $this->nodeMsg['modifyNodeName'],
            'server' => $this->nodeMsg['modifyNodeAddress'],
            'method' => $this->nodeMsg['modifyEncryptMethod'],
            'status' => $this->nodeMsg['modifyStatus'],
            'description' => $this->nodeMsg['modifyDescription'],
            'protocol' => $this->nodeMsg['modifyProtocol'],
            'obfuscation' => $this->nodeMsg['modifyObfuscation'],
        ]);*/
        $result  = DB::table('node_list')
            ->where('zone_id', $this->nodeMsg['nodeid'])
            ->update([
            'zone_name' => $this->nodeMsg['chineseNodeName'],
            'price' => $this->nodeMsg['price'],
            'name' => $this->nodeMsg['nodeNameGo'],
            'server' => $this->nodeMsg['nodeAddress'],
            'method' => $this->nodeMsg['encryptMethod'],
            'status' => $this->nodeMsg['status'],
            'description' => $this->nodeMsg['description'],
            'protocol' => $this->nodeMsg['protocol'],
            'obfuscation' => $this->nodeMsg['obfuscation'],
        ]);

        if ($result) {
            return '修改成功';
        } else {
            return '修改失败';
        }
    }
}