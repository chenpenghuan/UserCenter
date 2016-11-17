<?php
/**
 * Created by PhpStorm.
 * User: cph
 * Date: 16-11-17
 * Time: 下午3:14
 */

//程序执行入口
require_once 'UserController.class.php';
$jsonstr = file_get_contents("php://input");
$post = json_decode($jsonstr, true);
if ('login' == $post['act']) {
    $obj=new UserController($username = $post['username'], $password = $post['password'], $wait = 10, $errors = 5);
    $login_result = $obj->CheckLogin();
    //返回数组，array('username'=>$username,'islogin'=>$islogin)，其中login状态码为1标识登录成功，0标识用户名密码不匹配，-1标识登录失败次数过多，-2标识数据库连接错误
    if (is_array($login_result)) {
        echo json_encode($login_result);
    }
}
?>