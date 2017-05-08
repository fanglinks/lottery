<?php
class Libcurl{
     protected $_url; //请求url
     protected $_header; //请求header
     protected $_timeout;  //超时时间

     protected $_useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0 JaxWang/3.19'; // UA
     protected $_followLocation = true;
     protected $_proxy = '';

     protected $_maxRedirects = 4;
     protected $_referer = ''; //referer

     protected $_method = 'GET'; //请求类型 默认为GET
     protected $_contentData;     // 非GET时  请求的content数据
     protected $_raw = false; //非GET时 请求的content数据 是否是raw data 还是 form data 默认是formdata

     protected $_authentication = false; // http basic 认证开关 默认关闭
     protected $_auth_user = '';         // 认证帐号和密码

     protected $_cookieFile = false;                    //启用cookie开关  默认关闭
     protected $_cookieNamePrefix = '';
     protected $_cookieFileLocation = '/tmp/cookie/';   //cookie文件存放的位置 开关开启后生效


     protected $_includeHeader = false;   // 返回数据是否包含header的开关 默认不需要header
     protected $_headerInfo = '';         // 返回数据中header的信息   开关开启时才生效
     protected $_noBody = false;    // 返回的数据是否包含body的信息 默认返回
     protected $_content;           // body的信息  开关开启后才生效
     protected $_status;            // 返回http状态码
     protected $_curlerror;        //curl 错误信息
//   protected $_binaryTransfer = false;

     public function setUrl($url){
        $this->_url = $url;
     }
     /** 设置UA **/
     public function setUA($str){
        $this->_useragent = $str;
     }
     public function setFollowLocation($bool){
        $this->_followLocation = $bool;
     }
     /** 设置超时时间 **/
     public function setTimeout($int){
        $this->_timeout = $int;
     }

     public function setProxy($proxy){
        $this->_proxy = $proxy;
     }

     public function setMaxRedirects($int){
        $this->_maxRedirects = $int;
     }

     /** 开启http basic 认证 **/
     public function useAuth($bool = true){
        $this->_authentication = $bool;
     }

      /** 设置认证的帐号和密码 **/
     public function setAuthUser($name,$pass){
       $this->_auth_user = $name.':'.$pass;
     }

      /** 设置Referer **/
     public function setReferer($str){
       $this->_referer = $str;
     }

      /** 启用cookie **/
     public function useCookieFile($bool = true , $cookieNamePrefix = ''){
        $this->_cookieFile = $bool;
        $this->_cookieNamePrefix = $cookieNamePrefix;
     }

     /** 启用返回数据包含header信息 **/
     public function includeHeader($bool = true){
        $this->_includeHeader = $bool;
     }

     /** 构造函数 **/
     public function __construct($url = '',$header='',$timeout = 10){
        if($url) $this->setUrl($url);

        if($header){
            $this->_header = $header;
        }else{
            $this->_header = array();
        }

        $this->setTimeout($timeout);


     }

      /** curl发送请求 **/
     public function executeCURL(){

        if($this->_cookieFile){
            if(!is_file($this->_cookieFileLocation)){
                if(!is_dir($this->_cookieFileLocation)){
                    mkdir($this->_cookieFileLocation,0777,true);
                }
                $domain = preg_replace('/(http:\/\/|https:\/\/)/i','',$this->_url);
                $pos = strpos($domain,'/');
                if($pos){
                    $domain = substr($domain,0,$pos);
                }
                $this->_cookieFileLocation = $this->_cookieFileLocation. $domain . '_' . $this->_cookieNamePrefix . '.cookie';
            }
        }

        $s = curl_init();

        curl_setopt($s,CURLOPT_URL,$this->_url);
        curl_setopt($s,CURLOPT_HTTPHEADER,$this->_header);
        curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout);
        curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects);
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followLocation);
        curl_setopt($s,CURLOPT_HEADER,$this->_includeHeader);
        curl_setopt($s,CURLOPT_NOBODY,$this->_noBody);
        curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent);
        curl_setopt($s,CURLOPT_REFERER,$this->_referer);
        curl_setopt($s,CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        $ssl = (strtolower(substr($this->_url, 0, 8)) == 'https://') ? true : false;
        if($ssl){
            curl_setopt($s,CURLOPT_SSL_VERIFYHOST,false);
            curl_setopt($s,CURLOPT_SSL_VERIFYPEER,false);
        }
        if($this->_authentication) curl_setopt($s, CURLOPT_USERPWD, $this->_auth_user);

        if($this->_cookieFile){
            curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation);
            curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation);
        }

        if($this->_proxy){
            curl_setopt($s,CURLOPT_PROXY,$this->_proxy);
        }

        if($this->_method != 'GET'){
           if($this->_method == 'POST'){
               curl_setopt($s, CURLOPT_POST,true);
           }else{
               curl_setopt($s, CURLOPT_CUSTOMREQUEST,$this->_method);
           }
           if(!$this->_raw){
              $this->_contentData = http_build_query($this->_contentData);
           }
           curl_setopt($s, CURLOPT_POSTFIELDS,$this->_contentData);
        }

        $this->_content = curl_exec($s);

        $this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE);
        if($this->_includeHeader){
            $headerlen = curl_getinfo($s,CURLINFO_HEADER_SIZE);
            $this->_headerInfo = substr($this->_content, 0,$headerlen );
            //$this->_headerInfo  = explode("\n", trim($this->_headerInfo));
            $this->_content = substr($this->_content, $headerlen);
        }
        $this->_curlerror = curl_error($s);
        //监控
        if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] && !$this->_content && $this->_curlerror && strpos($this->_curlerror, 'host') > 0 ){
            $server_ip = $_SERVER['SERVER_ADDR'];
            $mkey = 'watch_curl_'.$server_ip;
            $arr = Mem::get($mkey);
            $loginfo = ['vist_link'=>'https://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], 'url'=> $this->_url, 'method' => $this->_method, 'http_status'=> $this->_status, 'curl_error' => $this->_curlerror, 'ip' => $server_ip];
            if($arr){
               $arr[] = $loginfo;
            }else{
               $arr = [];
               $arr[] = $loginfo;
            }
            Mem::set($mkey, $arr , 180);
            Log::info($loginfo, $mkey);
        }
        curl_close($s);
    }

    /** 发起GET请求 **/
    public function doGET($url=false){
        if($url) $this->setUrl($url);

        $this->_method = 'GET';
        $this->executeCURL();
    }

    /** 发起POST请求 **/
    public function doPOST($postArray,$raw = false,$url=false){
        if($url) $this->setUrl($url);

        $this->doRequest('POST',$postArray,$raw);
    }

    /** 发起PUT请求 **/
    public function doPUT($postArray,$raw = false,$url=false){
       if($url) $this->setUrl($url);

       $this->doRequest('PUT',$postArray,$raw);
    }

    /** 发起DELETE请求 **/
    public function doDELETE($postArray,$raw = false,$url=false){
        if($url) $this->setUrl($url);

        $this->doRequest('DELETE',$postArray,$raw);
    }

    protected function doRequest($method,$postArray,$raw){
        $this->_method = $method;
        $this->_raw = $raw;
        $this->_contentData = $postArray;
        $this->executeCURL();
    }

    /** 获取HTTP状态码 **/
    public function getStatus(){
       return $this->_status;
    }

     /** 获取返回数据中body内容 **/
    public function getBody(){
       return $this->_content;
    }

     /** 获取返回数据中header内容 **/
    public function getHeader(){
       return $this->_headerInfo;
    }
    public function getError(){
        return $this->_curlerror;
    }
}
