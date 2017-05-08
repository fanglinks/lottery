<?php
class Captcha{
    public static $api = 'http://rest.martin.buding.cn/captcha';
    public static function verify($captcha,$captchaid){
        $curl = new Libcurl(self::$api);
        $args = array(
            'r'     => $captchaid,
            'text'  => $captcha
            );
        $curl -> doPOST($args);
        $data = $curl -> getBody();
        $data = json_decode($data,true);
        if(isset($data['text']) && $data['text'] == $captcha){
            return true;
        }
        return isset($data['error']) ? $data['error']: 'noresponse';

    }
}
