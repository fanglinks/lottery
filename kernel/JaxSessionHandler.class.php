<?php
/**
  * php session rewrite
  * by wanghcunpeng
  **/

session_module_name('user');
session_name(SESSION_NAME);
session_set_cookie_params(COOKIEEXPIRETIME,'/',DOMAIN,false,true);

class JaxSessionHandler implements SessionHandlerInterface{
    private $memResource = null;

    public function open($savePath, $sessionName){
       // Log::info($savePath. " open -- ". $sessionName, 'session');
        $this->memResource = new Memcached();
        $this->memResource -> addServer(MEM_SESSION_HOST,MEM_SESSION_PORT);
        if(empty($this->memResource)){
            Log::error('could\'t open sesson , cause mem is null', 'session');
        }
        return true;
    }

    public function close(){
        $this->memResource -> quit();
        unset($this->memResource);
       // Log::info("close -- ", 'session');
        return true;
    }

    public function read($id){
        //Log::info("read -- ". $id, 'session');
        return (string)$this->memResource -> get(SESSIDPREFIX . $id);
    }

    public function write($id, $data){
        if(empty($this->memResource)){
            Log::error('could\'t write sesson , cause mem is null -- '. $id . ' -- '.$data, 'session');
            return false;
        }
        $this->memResource -> set(SESSIDPREFIX. $id,$data,SESSEXPIRETIME);
       // Log::info("write -- ". $id . ' -- '.$data, 'session');
        return true;

    }

    public function destroy($id){
        //Log::info("destroy -- ". $id , 'session');
        $this->memResource -> delete(SESSIDPREFIX . $id);
        return true;
    }

    public function gc($maxlifetime){
       // Log::info("gc -- ". $maxlifetime , 'session');
        return true;
    }
}
