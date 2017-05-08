<?php
class SocketWeiche{
    public $host ;
    public $port ;
    public $socket = null;

    public $packLen = 2;
    public $maxSize = 32768;

    public function __construct($host, $port){
        $this->host = $host;
        $this->port = $port;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->error();
        socket_connect($this->socket, $this->host, $this->port) or $this->error();
    }

    private function __readByLength($len,&$output){
      socket_recv($this->socket, $buf, $len ,0);
      $output.= $buf;
      $strlen = strlen($buf);
      if($len-$strlen != 0 ){
          return $this->__readByLength($len-$strlen,$output);
      }
      return $buf;
    }

    private function send(&$str){
        $piece = '';
        $len = strlen($str);
        if( $this->maxSize >= $len ){
            $piece = pack('n',$len) . $str;
            socket_send($this->socket, $piece , strlen($piece),0);
            $end = pack('n',0);
            socket_send($this->socket, $end , strlen($end),0);
        }else{
            $len = $this->maxSize;
            $tmp = substr($str,0,$len);
            $piece = pack('n',$len) . $tmp;
            $str = substr($str,$len);
            socket_send($this->socket, $piece , strlen($piece),0);
            $piece.= $this->send($str);
        }
        return $piece;
    }

    private function read(){
        $output = '';
        while(true) {
            socket_recv($this->socket,$buflen, $this->packLen ,0);
            $len = unpack('n', $buflen)[1];
            if($len !== 0){
               $this->__readByLength($len,$output);
            }else{
                break;
            }
        }
        return $output;
    }

    public function transfer($str){
        $stream = $this->send($str);
        $data = $this->read();
        return $data;
    }

    private function error(){
        $n = socket_last_error($this->socket);
        throw new ErrorException(socket_strerror($n), $n);
    }

    public function __destruct(){
        socket_close($this->socket);
    }
}
