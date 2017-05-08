<?php
class CURL {
    public static $header = array();
    public static $uAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0 JaxWang/3.19';
    public static $cookieFile = '/tmp/cookie.log';

    public static function setHeader($headers){
        self::$header = $headers;
    }

	public static function doRequest($method, $url, $vars,$returnstatus=false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$header);
		curl_setopt($ch, CURLOPT_USERAGENT, self::$uAgent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookieFile);
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,50); 
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}

		$data = curl_exec($ch);
		
        if($returnstatus){
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			return $http_status;
		}

        curl_close($ch);
        return $data;
	}

	public static function get($url,$returnstatus=false){
		return self::doRequest('GET', $url, 'NULL',$returnstatus);
	}

	public static function post($url, $vars,$returnstatus=false){
		return self::doRequest('POST', $url, http_build_query($vars),$returnstatus);
	}
	
	public static function raw_post($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, self::$header);
		curl_setopt($ch, CURLOPT_USERAGENT, self::$uAgent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
	}

	public static function getWX($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, @$_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,50); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: mp.weixin.qq.com'));
		$result = curl_exec($ch);
        curl_close($ch);
        return $result;
	}
}
