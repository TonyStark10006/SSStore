<?php
    header('Content-type: application/json;charset=utf-8');

    //过滤器
    function filter($data) {
        $data=trim($data);//通过 PHP trim() 函数去除用户输入数据中不必要的字符（多余的空格、制表符、换行）
        $data=stripslashes($data);//通过 PHP stripslashes() 函数删除用户输入数据中的反斜杠（\）
        $data=htmlspecialchars($data); //通过htmlspecialchars() 函数把特殊字符转换为 HTML 实体
        return $data;
    }

    /*
     * 定义输出返回内容函数，拼接封装成json字符串
     * return string json数据
     * */
    function outPutJson( $code, $msg, $data ){
        echo '{ "Code" : "'. $code . '" , "Message" : "' . $msg . '" , "Data" : ' . $data . ' }' ;
    }


    /*
     * 定义变量
     * @param string $code 状态码
     * @param string $msg 提示信息
     * @param string $data 返回数据
     * @param sting $userAppKey 自定义用于校验用户身份的KEY
     * @param string $appKey 请求中用于校验用户身份的KEY
     * @param string $name 请求中的提交的键值
     * */
    $code = 200;
    $msg = 'OK';
    $data = '';
    $userAppKey = '';
    $appKey = null;
    $name = null;


    try {

        if (!isset($_GET['appkey']) || !isset($_GET['name']) || empty($_GET['name']) || empty($_GET['appkey'])) {
            $code = 401;
            throw new Exception('appkey or keyvalue missed');
        }

        $appKey = filter($_GET['appkey']);
        $name = filter($_GET['name']);

        if ($appKey !== $userAppKey){

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
                    //将获得的数组形式的查询结果转成Jason格式数据，JSON_UNESCAPED_UNICODE选项作用是不以unicode编码中文，即显示“中文”而不是显示“/uXXX”
                    $data = json_encode($result,JSON_UNESCAPED_UNICODE);

                }


            }

    } catch (Exception $e) {
        //获取抛出的异常
        $msg = $e->getMessage();

    }

    outPutJson( $code, $msg, $data );


	exit();
	
	
?>