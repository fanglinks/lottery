<?php
class SMS{
	public static $api = 'http://rest.martin.buding.cn/push/sms';

	public static function pushSMS($phoneArr,$msg){
		$curl = new Libcurl(self::$api, array('Content-Type: application/json'));
		$data = array(
			array(
				'phones' => $phoneArr,
				'message' => $msg
			)
		);
		$curl -> doPOST(json_encode($data) ,true);
		$rt = json_decode($curl->getBody(),true);
		return $rt;
	}

	public static function checkSMSCode($phone,$code){
		$ssid = self::getSSID($phone);
		$codeObj = Mem::get($ssid);
		if(isset($codeObj['code']) && $codeObj['code'] == $code && $codeObj['phone'] == $phone){
			return true;
		}else{
			return false;
		}
	}

	public static function sendSMSCode($phone, $allow_times = 2 , $captcha_code = '' , $captcha_id = ''){

		$codeObj = array();
		$need_captcha = false;

		/*验证码机制*/
		if( $allow_times == 0 ){
			$need_captcha = true;
		}else{
			/**ip限制**/
			$ip = __ip();
			$mkey = 'sms-'.$ip;
			$vttimes = Mem::get($mkey);
			if(!$vttimes){
				$vttimes = 0;
			}
			if($vttimes >= $allow_times){
				$need_captcha = true;
			}else{
				$vttimes++;
				Mem::set($mkey, $vttimes, 43200); //12小时
			}

			/**手机号限制**/
			$mkey = 'sms-'.$phone;
			$vttimes = Mem::get($mkey);
			if(!$vttimes){
				$vttimes = 0;
			}
			if($vttimes >= $allow_times){
				$need_captcha = true;
			}else{
				$vttimes++;
				Mem::set($mkey, $vttimes, 43200); //12小时
			}
		}

		if($need_captcha){
			if(!$captcha_code){
				$codeObj['status'] = 'need_captcha';
			}else{
				if(Captcha::verify($captcha_code, $captcha_id) !== true){
					$codeObj['status'] = 'illegal_captcha';
				}
			}
		}

		/**发送短信**/
		if(!isset($codeObj['status'])){
			$time = 60; //sms life 60s
			$ssid = self::getSSID($phone);
			$codeObj = Mem::get($ssid);
			if($codeObj && isset($codeObj['timestamp']) && (time()-$codeObj['timestamp']) <= $time ){
				$codeObj['status'] = 'non-expired';
			}else{
				$rand = self::getRandNum();
				$str = '您好，您的验证码为：' . $rand . '。本验证码10分钟内有效。请填入验证码完成操作。';
				$job = self::pushSMS(array($phone) , $str);
				$codeObj['phone'] = $phone;
				if(isset($job['jid'])){
					$codeObj['code'] = $rand;
					$codeObj['timestamp'] = time();
					$codeObj['jobid'] = $job['jid'];
					Mem::set($ssid , $codeObj , 600); //10分钟后过期
					$codeObj['status'] = 'ok';
				}else{
					$codeObj['status'] = 'noresponse';
				}
			}
		}
		return $codeObj;
	}

	private static function getSSID($phone){
		return 'weiche_sms_' .$phone;
	}

	public static function destorySMSCode($phone){
		$ssid = self::getSSID($phone);
		Mem::remove($ssid);
	}

	private static function getRandNum(){
		return mt_rand(100000,999999);
	}

}
