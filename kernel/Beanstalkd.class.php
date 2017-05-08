<?php
include_once ROOT.'/kernel/Pheanstalk/Autoloader.php';
class Beanstalkd{
    const TUBEPREFIX = 'JAXWEB-';
    public static $pheanstalk = null;

    public function __construct(){
        self::getPheanstalk();
    }
    public static function getPheanstalk(){
        if(!self::$pheanstalk){
            Pheanstalk\Autoloader::register();
            try{
                self::$pheanstalk = new Pheanstalk\Pheanstalk(Config::BEANSTALK_HOST,Config::BEANSTALK_PORT);
            }catch(Exception $e){
                Log::error($e,'Beanstalkd');
            }
        }
        return self::$pheanstalk;
    }
    public static function putInTube(
        $tube,
        $data,
        $priority = null,
        $delay = null,
        $ttr = null
    ) {
    new Beanstalkd();
    $tube = self::TUBEPREFIX . $tube;
        try{
            $priority = $priority ? $priority : Pheanstalk\PheanstalkInterface::DEFAULT_PRIORITY;
            $delay = $delay ? $delay : Pheanstalk\PheanstalkInterface::DEFAULT_DELAY;
            $ttr = $ttr ? $ttr : Pheanstalk\PheanstalkInterface::DEFAULT_TTR;
            return self::$pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
       }catch(Exception $e){
            Log::error($e,'Beanstalkd');
       }
    }

   public static function peekBuried($tube = null){
	if($tube){
		$tube = self::TUBEPREFIX . $tube;
	}
	new Beanstalkd();
	try{
		return self::$pheanstalk->peekBuried($tube);
	}catch(Exception $e){
        	Log::error($e,'Beanstalkd');
        }
   }

   public static function kick($max){
	new Beanstalkd();
	try{
                return self::$pheanstalk->kick($max);
        }catch(Exception $e){
                Log::error($e,'Beanstalkd');
        }
   }

   public static function kickJob($job){
	new Beanstalkd();
        try{
                return self::$pheanstalk->kickJob($job);
        }catch(Exception $e){
                Log::error($e,'Beanstalkd');
        }
   }
}
