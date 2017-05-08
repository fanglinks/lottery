<?php
class Log {
    const LOG_NAME = 'log';

    const DEBUG_POSTFIX = 'debug';

    const INFO_POSTFIX = 'info';

    const ERROR_POSTFIX = 'error';

    const DATE_FORMATE = 'Y-m-d H:i:s';

    public static $fluent = null;
    public static $fluent_host = 'localhost';
    public static $fluent_port = '24224';
    public static $project_name = 'wx_weiche';

    private static function write($log, $log_file, $level){
        if ($log instanceof Exception) {
            $log = $log->getFile() . ' ' . $log->getLine() . ' ' . $log->getMessage();
        }
        self::initFlunet();

        $content_str = '';
        if( is_array($log) ){
            $content = $log;
            $content_str = var_export($log,true);
        }else if( is_object($log) ){
            $content = array($log);
            $content_str = var_export($log,true);
        }else{
            $content = array($log);
            $content_str = (string)$log;
        }

        if(in_array($level, array('debug', 'error')) && Sentry::$client){
            $sentry = clone Sentry::$client;
            $sentry -> logger = 'LogClass';
            $sentry -> captureMessage( $log_file . '.' . $level . ' | ' . $content_str  , null ,$level, true, $content);
        }

        try{

            self::$fluent->post('php.' . self::$project_name . '.' . $log_file . '|' . $level , $content );

        }catch(Exception $e){

            if(class_exists('Config',false)){
                $log_path = Config::LOG_PATH;
            }else{
                $log_path = '/tmp/log/';
            }
            if(!file_exists($log_path)) {
                if (!mkdir($log_path)) {
                    return;
                }
            }

            file_put_contents($log_path . 'flunet.error' , date(Log::DATE_FORMATE) . ' ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            $content_str = str_replace(PHP_EOL,' ', $content_str);
            file_put_contents($log_path . $log_file . '.' . $level , date(Log::DATE_FORMATE) . ' ' . $content_str . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    public static function debug($log, $log_file = Log::LOG_NAME) {
        Log::write($log, $log_file , Log::DEBUG_POSTFIX);
    }

    public static function info($log, $log_file = Log::LOG_NAME) {
        Log::write($log, $log_file , Log::INFO_POSTFIX);
    }

    public static function error($log, $log_file = Log::LOG_NAME) {
        Log::write($log, $log_file , Log::ERROR_POSTFIX);
    }

    public static function initFlunet(){
       if(!self::$fluent){
            $path = explode(DIRECTORY_SEPARATOR, ROOT);
            $count = count($path);
            self::$project_name = empty($path[$count-1]) ? $path[$count-2] : $path[$count-1];
            include_once ROOT.'/kernel/Fluent/Autoloader.php';
            Fluent\Autoloader::register();
            self::$fluent = Fluent\Logger\FluentLogger::open(self::$fluent_host,self::$fluent_port);
       }
    }
}
