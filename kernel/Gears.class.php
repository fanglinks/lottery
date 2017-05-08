<?php
class Gears{
    public $gearsVersion = '4.0';
    public static $socket = null;

    public function __construct(){
        if(!self::$socket){
            $api = explode(':', Config::SOCKET_API);
            self::$socket = new SocketWeiche($api[0], $api[1]);
        }
    }

    public function __call($method , $args){
        $args = isset($args[0])?$args[0]:array();
        $json = $this->buildJSON($method, $args);
        $data = self::$socket->transfer($json);
        if(!$data){
            throw new ErrorException('gears response null');
        }else{
            $data = json_decode($data, true);
            if(isset($data['error'])){
                Log::error($data,'gears');
            }
        }

        return $data;
    }

    private function buildJSON($method, $array_args){
        $jsonArr = array(
            'gears' => $this->gearsVersion,
            'method' => $method,
            'params' => $array_args
            );
        $json = json_encode($jsonArr);
        return $json;
    }
}
