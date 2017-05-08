<?php
class Platform{
	public static $wechat = false;
	public static $weiche = false;
	public static $alipay = false;
	public static $platform = '';
	public static $appname = '';
	public static $isHTTP = false;
	public static $isAJAX = false;
	public static $UA = '';

	public static function init(){

		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ){
		    self::$isAJAX = true;
		}

		if(isset($_SERVER['HTTP_HOST'])){
			self::$isHTTP = true;
			if(isset($_SERVER['HTTP_USER_AGENT'])){
			    self::$UA = $_SERVER['HTTP_USER_AGENT'];
			}else{
			    if (function_exists('__ip')) {
				Log::info(__ip().'  '.'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'illegal_request');
			    }
			}

			self::$platform = 'PC';

		    if(preg_match('/micromessenger/i', self::$UA)){
		        self::$platform = 'WeChat';
		    }else if(preg_match('/(alipayclient|alipaydefined)/i', self::$UA) ){
		        self::$platform = 'Alipay';
		    }else if(preg_match('/(iphone|ipad|ipod)/i',self::$UA)){
		        self::$platform = 'iOS';
		    }else if(preg_match('/android/i', self::$UA)){
		        self::$platform = 'Android';
		    }else if(preg_match('/windows phone/i', self::$UA)){
		        self::$platform = 'WinPhone';
		    }

		    if('WeChat' == self::$platform){
		        self::$wechat = true;
		    }

		    if(preg_match('/weiche/i', self::$UA)){
		        self::$weiche = true;
		    }

		    if('Alipay' == self::$platform){
		        self::$alipay = true;
		    }

		    if(self::$weiche && preg_match('/\sweiche([_a-zA-Z0-9\/\.]+)/i', self::$UA , $matches)){
		        self::$appname =  str_replace('__','app_',end($matches));
		    }

		}else{
			self::$platform = 'script';
		}

	}

	public static function setPlatform($platform){
		switch($platform){
			case 'wechat':
				self::$platform = 'WeChat';
				self::$wechat = true;
				self::$weiche = false;
				self::$alipay = false;
				break;
			case 'alipay':
				self::$platform = 'Alipay';
				self::$wechat = false;
				self::$weiche = false;
				self::$alipay = true;
				break;
			case 'weiche':
				self::$wechat = false;
				self::$weiche = true;
				self::$alipay = false;
				break;
			default:
				self::$wechat = false;
				self::$weiche = false;
				self::$alipay = false;
				break;
		}
	}

	public static function getFullPlatform(){
	    if(self::$appname){
	        return  self::$platform . '-'. self::$appname;
	    }else if(self::$weiche){
	        return self::$platform . '-app_weiche';
	    }else{
	        return self::$platform;
	    }
	}

	public static function inWeicheService(&$channel){
		if(self::$wechat || self::$alipay){
			$channel = strtolower(self::$platform);
			return true;
		}
		if(self::$weiche){
			$channel = 'weiche';
			return true;
		}
		return false;
	}
}
