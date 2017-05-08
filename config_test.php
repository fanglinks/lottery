<?php
error_reporting(E_ALL^E_STRICT);
//定义数据库和主机配置信息
define('HOST' , 'http://10.18.98.217/wx_weiche/');
// define('HOST' , 'http://localhost/wx_weiche/');
define('DB_PORT' , 3306);
define('DB_HOST' ,  '127.0.0.1');
define('DB_USER' ,  'root');
define('DB_PASS' ,  '000000');
define('DB_NAME' , 'wechat_weiche');

//定义memcache的配置信息
define('MEM_SESSION_HOST' , '127.0.0.1');
define('MEM_SESSION_PORT' ,  11211);

//定义session配置信息


class Config{
        const LOG_PATH = '/tmp/log/'; 
        const SENTRY_DSN = 'http://4993393e89ef4252b85ed6d540c46fea:83adc05b4cb44cf288878ee4e31e0277@sentry.shequan.com/10';
        // const SENTRY_DSN = 'http://5ca0257fb73a4f5695c90d77e6c2859a:928f9efb3d134d2781ea8979b9a6ee29@sentry.shequan.com/13';
        
        const BEANSTALK_HOST = '10.10.54.67';
        const BEANSTALK_PORT = 11300;
        // 测试api
        const MARTIN_RESTFUL_API = 'https://api.wcar.net.cn/test';
        const MARTIN_THRIFT_API = 'martin-test.buding.cn';
        const MARTIN_GEARS_API = 'http://gears-test.shequan.com';
        // const MARTIN_GEARS_API = 'http://127.0.0.1:9300';
        // //rc api
        // const MARTIN_RESTFUL_API = 'https://api.wcar.net.cn/test';
        // const MARTIN_THRIFT_API = 'martin-test.buding.cn';
        // const MARTIN_GEARS_API = 'http://gears-test.shequan.com';
        //线上api
        // const MARTIN_RESTFUL_API = 'https://api.wcar.net.cn';
        // const MARTIN_THRIFT_API = 'martin.buding.cn';
        // const MARTIN_GEARS_API = 'http://gears.shequan.com';

        public static function getMemConfig(){
            $servers = array(
                array('127.0.0.1','11211',1)
            );
            return $servers;
        }
        public static function getSlaveDBconfig(){
            return array('10.10.79.182','wechat_weiche','o2cpwang319','pwd@501333679');
        }
        public static function getMartinDBconfig(){
            return array('127.0.0.1','martin','root','000000');
        }
}
