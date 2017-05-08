<?php
 class Mem {
        public static  $memtime = 10800; //3 hours
        private static $CLASS_MEM = null;
        private static $mem_connection = null;

        private static function init(){
            if(!self::$CLASS_MEM){
                $servers = array();
                if(class_exists('Config',false)){
                    $servers = Config::getMemConfig();
                }else{
                    $servers[] = array(MEM_HOST,MEM_PORT,1);
                }
                self::$CLASS_MEM = new Memcached();
                self::$CLASS_MEM->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);  //分布式算法采用CONSISTENT(一致性hash校验)
                self::$CLASS_MEM->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC);  //指定存储元素key使用的hash算法
                self::$mem_connection = self::$CLASS_MEM->addServers($servers);
            }
        }
        public static function get($mkey){
            self::init();
            if(self::$mem_connection){
                return self::$CLASS_MEM->get($mkey);
            }
            return false;
        }
        public static function set($mkey,$data,$mtime=''){
            self::init();
            if($mtime === ''){
                $mtime=self::$memtime;
            }
            if(self::$mem_connection){
                return self::$CLASS_MEM->set($mkey,$data,$mtime);
            }
            return false;
        }
        public static function remove($mkey){
            self::init();
            if(self::$mem_connection){
                 self::$CLASS_MEM->delete($mkey);
            }
        }

        public static function remember($mkey, $mtime, \Closure $callback, $do = false) {
            if (($value = self::get($mkey)) && !$do) {
                return $value;
            }
            self::set($mkey, $value = $callback(), $mtime);
            return $value;
        }
        /**
        *乐观锁
        */
        public static function getCas($mkey,$value,$mtime=''){
            self::init();
            if(self::$mem_connection){
                 self::$CLASS_MEM->get($mkey,null,$cas);
                 if (self::$CLASS_MEM->getResultCode() == Memcached::RES_NOTFOUND) {
                    self::set($mkey,$value,$memtime);
                    return true;
                }else {
                     self::$CLASS_MEM->get($mkey,null,$cas);
                    return $cas;
                }
            }
        }
        /**
        *乐观锁
        */
        public static function setCas($mkey,$cas,$value){
            self::init();
            if(self::$mem_connection){
                return self::$CLASS_MEM->cas($cas,$mkey,$value);
            }
        }

        public static function setSessionStore($user_id,$type){
            $ssid = session_id();
            $data = json_encode($_SESSION);
            $sql = 'INSERT INTO `session_store` (`ssid` ,`user_id`,`data`,`type` , `ua`) VALUES ( ?,?,?,?,?)'; //ON DUPLICATE KEY UPDATE `data` = ?, `type` = ?, `user_id` = ?, `ua` = ?
            DB::exec($sql,array($ssid, $user_id, $data, $type, Platform::$UA)); //, $data, $type, $user_id, Platform::$UA
        }

        public static function getSessionStore(){
            $ssid = session_id();
            $data = DB::getCell("select data from `session_store` where ssid = ?",array($ssid));
            if($data){
                $data = json_decode($data,true);
                $_SESSION = array_merge($_SESSION,$data);
            }
        }

        public static function setUnique($arr_keys, $key_prefix, $time = 10){
            if(!is_array($arr_keys)){
                $arr_keys = array($arr_keys);
            }

            foreach($arr_keys as $v){
                $key = $key_prefix.'uiq'. $v;
                Mem::set($key, true, $time);
            }

        }

        public static function checkUnique($arr_keys, $key_prefix){
            if(!is_array($arr_keys)){
                $arr_keys = array($arr_keys);
            }
            foreach($arr_keys as $v){
                $key = $key_prefix.'uiq'. $v;
                if(Mem::get($key)){
                    return true;
                }else{
                    continue;
                }
            }
            return false;
        }

 }
