<?php

class UserController
{
    private $username;
    private $password;
    private $wait;
    private $errors;

    public function __construct($username, $password, $wait, $errors)
    {
        $this->username = $username;
        $this->password = $password;
        $this->wait = $wait;
        $this->errors = $errors;
    }

    public function CheckLogin()
    {
        if ($this->username && $this->password) {
            $islogin = 0;
            $redis = new Redis();
            $redis->connect('localhost', 6379);
            $redis->auth('123123');
            $ip = $this->getIp();
            if ($redis->get($ip)) {
                if ($redis->get($ip) > $errors - 1) {
                    $redis->set($ip, $redis->get($ip), $wait);
                    $islogin = -1;//登录失败次数过多，禁止登录$wait秒
                } else {
                    $redis->set($ip, $redis->get($ip) + 1);
                }
            } else {
                $redis->set($ip, 1);
            }
            if ($islogin == 0) {
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=cph", "root", "123123");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = 'select username from user where username="' . $this->username . '" and password="' . md5($this->password) . '"';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $islogin = 1;//登录成功
                        $redis->del($ip);
                    }
                } catch (PDOException $e) {
                    $islogin = -2;    //数据库连接错误
                }
            }
            $result = array('username' => $this->username, 'islogin' => $islogin);
        }
        return $result ? $result : false;
    }

// 获取IP地址（摘自discuz）
    private function getIp()
    {
        $ip = '未知IP';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $this->is_ip($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $ip;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $this->is_ip($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
        } else {
            return $this->is_ip($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $ip;
        }
    }

//校验ip是否有效
    private function is_ip($str)
    {
        $ip = explode('.', $str);
        for ($i = 0; $i < count($ip); $i++) {
            if ($ip[$i] > 255) {
                return false;
            }
        }
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
    }
}

?>
