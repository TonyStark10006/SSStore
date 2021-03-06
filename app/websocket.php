<?php
//namespace App;

//use swoole_websocket_server;
//use swoole_table;

//创建Swoole表格，用于储存用户ID跟SwooleID的对应关系
$user_table = new swoole_table(2048);
$user_table->column('user_id', swoole_table::TYPE_INT);
$user_table->column('username', swoole_table::TYPE_STRING, 64);
$user_table->column('swoole_id', swoole_table::TYPE_INT);
$user_table->create();

$ws = new swoole_websocket_server("0.0.0.0", 9501);


$ws->on('open', function ($ws, $request) {
    //
});

$ws->on('message', function ($ws, $frame) use ($user_table) {
    print_r($frame->data);
    echo "\n";

    //获取发送人提交的所有信息，包括发送人个人信息、发送内容和接收人信息
    $receiverMsg = json_decode($frame->data);
    print_r($receiverMsg->type);
    echo "\n";

    /*
     * 根据用户发送消息类型进行相应处理
     * 'int' => 初始化，将用户ID、username写入Swoole内存表
     * 'chatMessage' => 聊天数据，根据接收人用户名从Swoole内存表得到对应的接收人SwooleID，然后推送
     * */
    switch ($receiverMsg->type) {
        case 'chatMessage':

            //获取13位时间戳，服务器发送消息时间
            list($t1, $t2) = explode(' ', microtime());
            $timestamp = sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
            //settype($timestamp, 'int');

            //推送给接收人的消息内容
            $returnMsg = array();
            $returnMsg['username'] = $receiverMsg->data->mine->username;
            $returnMsg['avatar'] = $receiverMsg->data->mine->avatar;

            //根据聊天类型赋值相应ID，客户端根据ID判断消息来源
            if ($receiverMsg->data->to->type == 'friend') {
                $returnMsg['id'] = $receiverMsg->data->mine->id;
            }
            if ($receiverMsg->data->to->type == 'group') {
                $returnMsg['id'] = $receiverMsg->data->to->id;
            }
            $returnMsg['type'] = $receiverMsg->data->to->type;
            $returnMsg['content'] = $receiverMsg->data->mine->content;
            $returnMsg['mine'] = false;
            $returnMsg['timestamp'] = (int) $timestamp;//1499938420198

            //处理单聊消息
            if ($receiverMsg->data->to->type == 'friend') {
                $receiver = $user_table->get($receiverMsg->data->to->username, 'swoole_id');
                //推送消息给接收人
                $ws->push($receiver, json_encode($returnMsg, JSON_UNESCAPED_UNICODE));//"{$frame->data}"
                print_r(json_encode($returnMsg, JSON_UNESCAPED_UNICODE));
                echo "\n";

            } /*else {
                //保存离线消息

            }*/

            //处理群聊
            if ($receiverMsg->data->to->type == 'group') {
                foreach ($ws->connections as $allFd) {
                    if ($frame->fd !== $allFd) {
                        $ws->push($allFd, json_encode($returnMsg, JSON_UNESCAPED_UNICODE));
                    }
                }
                print_r(json_encode($returnMsg, JSON_UNESCAPED_UNICODE));
                echo "\n";
            }
            break;

        case 'int':
             $user_table->set($receiverMsg->data->username, array(
                'user_id' => $receiverMsg->data->id,
                'username' => $receiverMsg->data->username,
                'swoole_id' => $frame->fd
            ));

            $user_table->set($frame->fd, array(
                'user_id' => $receiverMsg->data->id,
                'username' => $receiverMsg->data->username,
                'swoole_id' => $frame->fd
            ));

             print_r($user_table->get($receiverMsg->data->username));
             print_r($user_table->get($frame->fd));
             break;
}
});




$ws->on('close', function ($ws, $fd) use ($user_table) {
    /*foreach($server->connections as $fd) {
        $server->push($fd, json_encode($data));
    }*/
    $delTarget = $user_table->get($fd)['username'];
    $user_table->del($delTarget);
    $user_table->del($fd);

    echo "ID:{$fd}的客户端已经关闭\n";
});

$ws->start();
