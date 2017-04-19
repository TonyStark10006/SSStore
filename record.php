<?php
    header('Content-type: application/json;charset=utf-8');

    //过滤器
    function filter($data) {
        $data=trim($data);//通过 PHP trim() 函数去除用户输入数据中不必要的字符（多余的空格、制表符、换行）
        $data=stripslashes($data);//通过 PHP stripslashes() 函数删除用户输入数据中的反斜杠（\）
        $data=htmlspecialchars($data); //通过htmlspecialchars() 函数把特殊字符转换为 HTML 实体
        return $data;
    }

    //定义输出返回内容函数，拼接成json字符串
    function outPutJson( $code, $data, $msg ){
        echo '{ "Code" : "'. $code . '" , "Message" : "' . $msg . '" , "Data" : ' . $data . ' }' ;
    }


    //定义返回变量
    $code = 200;
    $msg = 'ok';
    $data = '';
    $userAppKey = '';


    try {

        if (!isset($_GET['appkey']) || !isset($_GET['name']) || empty($_GET['name']) || empty($_GET['appkey'])) {
            $code = 401;
            throw new Exception('appkey or keyvalue missed');
        }

        $appkey = filter($_GET['appkey']);
        $name = filter($_GET['name']);

        if ($appkey !== $userAppKey){

            $code = 402;
            throw new Exception('appkey incorrect');

            } else {

                //数据库连接信息
                $username="";
                $password="";
                $dbname="";
                $servername='localhost';


                $conn=new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);

                //设置PDO错误模式为抛出 exceptions 异常
                $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


                $sql = "SELECT name,mobile,address FROM table WHERE name = '$name'";//''%'.$name.'%''

                $result = $conn->query($sql)->fetchAll(PDO::FETCH_CLASS);

                $conn=null;

                if (empty($result)){

                    $code = 404;
                    throw new Exception('no result');

                } else {
                    //将获得的数组形式的查询结果转成Jason格式数据
                    $data = json_encode($result,JSON_UNESCAPED_UNICODE);

                }


            }

    } catch (Exception $e) {
        //获取抛出的异常
        $msg = $e->getMessage();

    }

    outPutJson( $code, $data, $msg );


	exit();
	
	
?>