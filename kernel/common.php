<?php
define('UI', ROOT . 'front/ui/');
define('VUE', ROOT . 'vue/dist/');
define('LICENSE_PREFIX', '京,沪,津,冀,晋,蒙,辽,吉,黑,苏,浙,皖,闽,赣,鲁,豫,鄂,湘,粤,桂,琼,渝,川,贵,云,藏,陕,甘,青,宁,新');

ini_set('session.serialize_handler', 'php_serialize');
date_default_timezone_set('Asia/Shanghai');
mb_internal_encoding("UTF-8");
spl_autoload_register('Jax_autoload');
if( defined('Config::SENTRY_DSN') ){
    Sentry::autoload();
}
Platform::init();
DB::setBait('master', array(DB_HOST,DB_NAME,DB_USER,DB_PASS,DB_PORT));


if(!Platform::$isHTTP){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    define('DOMAIN', 'wx.wcar.net.cn');
    $_SERVER['HTTP_HOST'] = HOST;
    $_SERVER['REQUEST_URI'] = '';
    $_SERVER['HTTP_USER_AGENT'] = '';

}else{

    define('PAGESELF', 'https://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    define('DOMAIN', $_SERVER['HTTP_HOST']);

    //allow crossdomain
    $ORIGNLIST = array('http://bz.shequan.com','https://wx.wcar.net.cn','http://wx.weiche.me','https://web-test.wcar.net.cn','http://test.pphongbao.com','https://oneshop.pphongbao.com');
    if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'] , $ORIGNLIST)){
        header('Access-Control-Allow-Origin: ' .$_SERVER['HTTP_ORIGIN'] );
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); //  缓存一天
    }

    if(!defined('DISABLESESSION')){
        define('SESSIDPREFIX' , 'mem.' . DOMAIN . '.');
        define('SESSEXPIRETIME' ,  2591999);  // 30days - 1s
        define('COOKIEEXPIRETIME' ,  31536000);  // 1 year

        if(__gett('channel_weiche')){
            define('SESSION_NAME', 'WEICHE_' . str_replace('.','_',DOMAIN) . '_' . __gett('channel_weiche') );
        }else{
            define('SESSION_NAME', 'WEICHE_' . str_replace('.','_',DOMAIN) );
        }

        /*
        define('SESSIDPREFIX' , 'jax.weiche.ssid.');
        define('SESSEXPIRETIME' , 604800);  // 1 week or 7 days
        define('SESSION_NAME', 'WEICHESESSIDBYJAX');
        */
        $my_handler = new JaxSessionHandler();
        session_set_save_handler($my_handler, true);
        session_start();
        //$arr = session_get_cookie_params();
    }
}

function Jax_autoload($class_name) {
    $FILE_PATH = array();
    $FILE_PATH['kernel'] = ROOT.'kernel/' . $class_name . '.class.php';
    $FILE_PATH['module'] = ROOT.'module/' . $class_name . '.class.php';

    foreach($FILE_PATH as $k=>$v){
        if(is_file($v) && is_readable($v)){
            require $v;
            return true;
        }
    }
    return false;
}

function _p($obj,$var_dump=false){
    echo "rt:<pre>";
    if($var_dump){
        var_dump($obj);
    }else{
        print_r($obj);
    }
    echo "</pre>";
}

function filter($str,$deltags = false){
    $str = addslashes($str);
    if($deltags){
        $str = strip_tags($str);
    }
    return $str;
}

function getParameter($key, $default = false) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } else if (false === $default) {
        $log = "Missing the parameter $key.";
        Log::error($log);
        die($log);
    } else {
        return $default;
    }
}

function __val($obj, $key , $default = ''){
    return isset($obj[$key])?$obj[$key]:$default;
}
function __post($key,$default=''){
    return isset($_POST[$key])?$_POST[$key]:$default;
}
function __gett($key,$default=''){
    return isset($_GET[$key])?$_GET[$key]:$default;
}
function __reqt($key,$default=''){
    return isset($_REQUEST[$key])?$_REQUEST[$key]:$default;
}

function __gbk($str,$ignore = true){
    $gbk = 'GBK';
    if($ignore){
        $gbk.= '//IGNORE';
    }
    return iconv('UTF-8',$gbk,$str);
}
function __utf8($str,$ignore = true){
    $utf8 = 'UTF-8';
    if($ignore){
        $utf8.='//IGNORE';
    }
    return iconv('GBK',$utf8,$str);
}
function __week($w,$prefix = '周'){
    $zh_cn = array(
        0 => '日',
        1 => '一',
        2 => '二',
        3 => '三',
        4 => '四',
        5 => '五',
        6 => '六'
        );
    if(isset($zh_cn[$w])){
        return $prefix . $zh_cn[$w];
    }
    return false;
}
/** these functions was added ***/

function __ip(){
    $ip = '124.207.11.6';
    if(isset($_SERVER['HTTP_X_REAL_IP'])){
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !$ip){
        $iplist = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = $iplist[count($iplist)-1];
    }else{
        $ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$ip;
    }
    return $ip;
}
function clientIP(){
    return __ip();
}

function hextostr($x) {
  $s='';
  foreach(explode("\n",trim(chunk_split($x,2))) as $h) $s.=chr(hexdec($h));
  return($s);
}

function pkcs5_unpad($text){
    $pad = ord($text{strlen($text)-1});
    if ($pad > strlen($text)) return $text;
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return $text;
    return substr($text, 0, -1 * $pad);
}

function pkcs5_pad($text){
    $len = strlen($text);
    $mod = $len % 8;
    $pad = 8 - $mod;
    return $text.str_repeat(chr($pad),$pad);
}

function encrypt($encrypt ,$key) {
    $key = substr($key,0,8);
    $encrypt = pkcs5_pad($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
    $passcrypt = mcrypt_encrypt(MCRYPT_DES, $key, $encrypt, MCRYPT_MODE_ECB, $iv);
    return bin2hex($passcrypt);
}

function decrypt($decrypt,$key) {
    $decoded = pack("H*", $decrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
    $decrypted = mcrypt_decrypt(MCRYPT_DES, $key, $decoded, MCRYPT_MODE_ECB, $iv);
    return pkcs5_unpad($decrypted);
}

function weightedRand($probArr){
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($probArr);

    //概率数组循环
    foreach($probArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if($randNum <= $proCur) {
            $result = $key;
            break;
        }else{
            $proSum -= $proCur;
        }
    }
    unset ($probArr);
    return $result;
}

function IDCard(&$idno){
    $rt = array(
        'status'=>false,
        'msg' => ''
        );
    $idno = strtoupper($idno);
    if(preg_match("/^\d{17}[0-9|X]$/i",$idno)){
        $birth_year = substr($idno,6,4);
        $birth_month = substr($idno,10,2);
        $birth_date = substr($idno,12,2);

        if((int)$birth_month > 12 || (int)$birth_date > 31 ){
            $rt['status'] = false;
            $rt['msg'] = '身份证号不合法';
            return $rt;
        }

        $arr = preg_split('//',$idno,-1,PREG_SPLIT_NO_EMPTY);

        $sum = 0;
        $vc = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        for ($i = 0; $i < 17; $i++) $sum += $vc[$i] * (int)($arr[$i]);
        $last = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2')[$sum % 11];
        if($arr[17] != $last){
            $rt['status'] = false;
            $rt['msg'] = '该身份证不合法';
            $rt['last'] = $last;
            return $rt;
        }
        $rt['status'] = true;
        $rt['birth_year'] = $birth_year;
        $rt['birth_month'] = $birth_month;
        $rt['birth_date'] = $birth_date;
        $sex = substr($idno,-2,1);
        if($sex%2 == 0){
            $rt['sex'] = "Female";
        }else{
            $rt['sex'] = "Male";
        }
        return $rt;
    }else{
        $rt['status'] = false;
        $rt['msg'] = '身份证输入有误';
        return $rt;
    }

}
function array_sort($arr,$keys,$type='desc'){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$keys];
    }
    if($type == 'desc'){
        arsort($keysvalue);
    }else{
        asort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}
/**
 * 设置HTTP状态码
 * 所有消息值和文本均来自于百度百科，HTTP状态码http://baike.baidu.com/view/1790469.htm
 * @access public static
 * @param int $status 状态码值，如：301
 * @return string 成功返回true，失败返回false
 */
function setHttpStatus($status){
    $httpStatus = array(
            // 消息（1字头）
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            // 成功（2字头）
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            // 重定向（3字头）
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Temporarily Moved',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy', // 在最新版的规范中，306状态码已经不再被使用
            307 => 'Temporary Redirect',
            // 请求错误（4字头）
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            421 => 'There are too many connections from your internet address',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With', // 由微软扩展，代表请求应当在执行完适当的操作后进行重试。
            // 服务器错误（5字头）
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            600 => 'Unparseable Response Headers',
    );
    if(isset($httpStatus[$status])){
        header("HTTP/1.1 $status $httpStatus[$status]");
        return true;
    }else{
        return false;
    }
}
