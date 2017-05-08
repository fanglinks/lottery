<?php
class DB{
    private static $_Logging = true;
    private static $_PDOpool = null;
    private static $_Harpoon = 'master';
    private static $_Bait = null;

    public static function setLogging($logging) {
        self::$_Logging = $logging;
    }

    private static function logInfo($info){
        if (self::$_Logging){
            $test = array();
            $test['sql'] = $info;
            /*
            $test['from'] = $_SERVER;
            $test['reqt'] = $_REQUEST;
            */
            Log::info($test,'sql_log');
        }
    }

    public static function setBait($key,$dbconfig){
        $dbconfig[4] = isset($dbconfig[4]) ? $dbconfig[4] : 3306;
        self::$_Bait[$key] = array(
                'DB_HOST' => $dbconfig[0],
                'DB_NAME' => $dbconfig[1],
                'DB_USER' => $dbconfig[2],
                'DB_PASS' => $dbconfig[3],
                'DB_PORT' => $dbconfig[4]
                );
    }

    public static function setHarpoon($key = 'master'){
        self::$_Harpoon = $key;

        if( !isset(self::$_Bait[self::$_Harpoon]) || !isset(self::$_Bait[self::$_Harpoon]['DB_HOST'] ) || !self::$_Bait[self::$_Harpoon]['DB_HOST'] ){
            self::setBait($key,array(DB_HOST,DB_NAME,DB_USER,DB_PASS,DB_PORT));
        }
    }

    public static function getBait(){
        return self::$_Bait;
    }

    public static function getHarpoon(){
        return self::$_Harpoon;
    }
    /**
    *pdo 返回连接信息 事务处理
    */
    public static function getNewObgect(){
        self::getInstance();
        return self::$_PDOpool[self::$_Harpoon];
    }
    private static function getInstance(){
        if(!isset(self::$_Bait[self::$_Harpoon]) || !self::$_Bait[self::$_Harpoon]){
            self::setHarpoon();
        }
        if(!isset(self::$_PDOpool[self::$_Harpoon]) || !self::$_PDOpool[self::$_Harpoon] ){
            try{
                self::$_PDOpool[self::$_Harpoon] = new PDO(
                        'mysql:host=' . self::$_Bait[self::$_Harpoon]['DB_HOST'] . ';port=' . self::$_Bait[self::$_Harpoon]['DB_PORT'] . ';dbname=' . self::$_Bait[self::$_Harpoon]['DB_NAME'] . ';charset=utf8',
                        self::$_Bait[self::$_Harpoon]['DB_USER'],
                        self::$_Bait[self::$_Harpoon]['DB_PASS']
                        );
                self::$_PDOpool[self::$_Harpoon]->exec('set names utf8');
                self::$_PDOpool[self::$_Harpoon]->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$_PDOpool[self::$_Harpoon]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            }catch(PDOException $e){
                self::handlePDOEx($e, __FUNCTION__, func_get_args() );
            }

            if(!self::$_PDOpool[self::$_Harpoon]){
                Log::error('connect faild, PDO is null');
                die('<h1 style="text-align:center;color:#888;padding-top:20px">loading</h1><script>location.reload();</script>');
            }
        }
    }

    /**
desc : 执行sql语句 , 应用场景 update , insert
param:
$sql 要执行的sql语句(预处理) 必填
$data 预处理sql语句中的变量 选填   默认null
$lastInsertId 如果是执行insert操作,当该值为true时会返回insert后的自增id ;如果是执行update,设置该值为true后会返回0    默认false
return: (int)lastInsertId or (void)无返回
     **/
    public static function exec($sql,$data=null,$lastInsertId=false){
        self::getInstance();
        $args = is_null($data)?'':implode(',',$data);
        try{
            $statement = self::$_PDOpool[self::$_Harpoon]->prepare($sql);
            $statement->execute($data);
            self::logInfo($sql.'--args:'.$args);
            if($lastInsertId){
                return self::$_PDOpool[self::$_Harpoon]->lastInsertId();
            }
        }catch(PDOException $e){
            return self::handlePDOEx($e, __FUNCTION__, func_get_args() );
        }
    }

    /**
desc : 获取查询语句的结果集 , 应用场景 select
param:
$sql 要执行的sql语句(预处理) 必填
$data 预处理sql语句中的变量 选填 默认null
return: (array)查询后的结果集合
     **/
    public static function getAll($sql,$data=null){
        self::getInstance();
        $args = is_null($data)?'':implode(',',$data);
        try{
            $statement = self::$_PDOpool[self::$_Harpoon]->prepare($sql);
            $statement->execute($data);
            self::logInfo($sql.'--args:'.$args);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            return self::handlePDOEx($e, __FUNCTION__, func_get_args() );
        }
    }

    /**
desc : 获取查询语句的一条结果 , 应用场景 select * from table where id=1;
param:
$sql 要执行的sql语句(预处理) 必填
$data 预处理sql语句中的变量 选填 默认null
return: (array)查询后的一条结果
     **/
    public static function getOne($sql,$data=null){
        self::getInstance();
        $args = is_null($data)?'':implode(',',$data);
        try{
            $statement = self::$_PDOpool[self::$_Harpoon]->prepare($sql);
            $statement->execute($data);
            self::logInfo($sql.'--args:'.$args);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        }catch(PDOException $e){
            return self::handlePDOEx($e, __FUNCTION__, func_get_args() );
        }
    }

    /**
desc : 获取查询结果的单个值, 应用场景 select count(id)
param:
$sql 要执行的sql语句(预处理) 必填
$data 预处理sql语句中的变量 选填 默认null
return: (mix)查询后的单个值
     **/
    public static function getCell($sql,$data=null) {
        self::getInstance();
        $args = is_null($data)?'':implode(',',$data);
        try{
            $statement = self::$_PDOpool[self::$_Harpoon]->prepare($sql);
            $statement->execute($data);
            self::logInfo($sql.'--args:'.$args);
            $result = $statement->fetchColumn();
            return $result;
        }catch(PDOException $e){
            return self::handlePDOEx($e, __FUNCTION__, func_get_args() );
        }
    }

    public static function clear(){
        self::$_PDOpool[self::$_Harpoon] = null;
        unset(self::$_PDOpool[self::$_Harpoon]);
    }

    private static function handlePDOEx($e,$func,$arg_arr){
        if(strpos($e->getMessage(), 'MySQL server has gone away') !== false ){
            self::clear();
            return call_user_func_array('DB::' . $func , $arg_arr);
        }else{
            Log::error(array('DB::'.$func,$arg_arr,$e));
        }
    }

}

