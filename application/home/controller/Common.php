<?php
namespace app\home\controller;

use think\facade\Cache;
use think\Controller;
class Common extends Controller {
	
    protected function initialize()
    {
        $user_name = 'owen';
        $passowrd  = md5(md5('123456')+123456);
        $userId = 3975;
        $lifeTime  = time()+30*24*3600;
    	setcookie('name',$user_name,$lifeTime);
        setcookie('password',$passowrd,$lifeTime);
        setcookie('userId',$userId,$lifeTime);
        //连接本地的 Redis 服务
//        $redis = new Redis();

//        $redis->set('test','ouyang');
        // 获取存储的数据并输出

//        setcookie($user_name, $passowrd, $userId, time() + $lifeTime, "/");
    }

}
