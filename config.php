<?php
define('ROOT', substr(__FILE__,0,strrpos(__FILE__, '/')+1));
if(!is_file( ROOT . 'config_test.php')){
    if(isset($_SERVER['HTTP_HOST'])){
        define('HOST', 'https://'. $_SERVER['HTTP_HOST'].'/');
    }else{
        define('HOST', 'https://wx.wcar.net.cn/');
    }
    define('DB_PORT' , 3306);
    define('DB_HOST' , '10.10.68.214');
    define('DB_USER' , 'o2cpwang319');
    define('DB_PASS' , 'pwd@501333679');
    define('DB_NAME' , 'annual');
    define('MEM_SESSION_HOST','10.10.69.251');
    define('MEM_SESSION_PORT',11211);

    class Config{
        const LOG_PATH = '/data/app/wx_weiche/';
        const MARTIN_RESTFUL_API = 'http://restful.nginx.service.consul';
        const MARTIN_THRIFT_API = 'martin.buding.cn';
        const MARTIN_BUDING_API = 'http://api.buding.cn';
        const MARTIN_GEARS_API = 'http://gears.shequan.com';
        const BEANSTALK_HOST = '10.10.54.67';
        const BEANSTALK_PORT = 11300;
        const MONGODB_DSN = 'mongodb://martin_v8:LpdkUKRIFp@10.10.84.118:27017/martin';
        const WEB_MONGODB_DSN = "mongodb://weiche_web:ucloudbuding@10.10.114.253:27017/oilstation";
        const SENTRY_DSN = 'http://76bc8d7f46a341308bc1216c68b31fd2:3a0e0b46f8a74096a6d92eb30cd8ba7e@sentry.shequan.com/2';
        const SOCKET_API = '10.10.112.177:7300';
        const REFINERY_API = 'https://oil.wcar.net.cn';
        public static function getMemConfig(){
            $servers = array(
                    array('10.10.74.248',11211,2),
                    array('10.10.69.251',11211,1)
                    );
            return $servers;
        }
        public static function getSlaveDBconfig(){
            return array('10.10.79.182','wechat_weiche','o2cpwang319','pwd@501333679');
        }
        public static function getMartinDBconfig(){
            return array('martin-base-slave-read.mysql.service.consul','martin','wangchunpeng','pwd@501333679');
        }
    }
}else{
    require 'config_test.php';
}
require(ROOT.'kernel/common.php');
require(ROOT.'module/common.php');
