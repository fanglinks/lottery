<?php
class URL {
    // private static $mcrypt_key = md5('Buding Weiche in Wechat by Jax Wang(wangchunpeng)');
    const MCRYPT_KEY = '1345c5c6a3d0b29f23a4d5876ac35d89';

    private static function encrypt($decrypted_text) {
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $td_size = mcrypt_enc_get_iv_size($td);
        $iv = mcrypt_create_iv($td_size, MCRYPT_RAND);
        $key = substr(self::MCRYPT_KEY, 0, $td_size);
        mcrypt_generic_init($td, $key, $iv);
        $encrypted_text = base64_encode(mcrypt_generic($td, $decrypted_text));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $encrypted_text;
    }

    private static function decrypt($encrypted_text) {
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $td_size = mcrypt_enc_get_iv_size($td);
        $iv = mcrypt_create_iv($td_size, MCRYPT_RAND);
        $key = substr(self::MCRYPT_KEY, 0, $td_size);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_text = trim(mdecrypt_generic($td, base64_decode($encrypted_text)));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $decrypted_text;
    }

    public static function uri($uri){
        if(strstr($uri ,'=') === false){
            $uri = 'vid='.$uri;
        }
        parse_str($uri,$array);
        $json = json_encode($array);
        return 'args='.urlencode(self::encrypt($json));
    }

    public static function args(){
        $json = isset($_REQUEST['args'])?self::decrypt($_REQUEST['args']):'';
        if($json){
            return json_decode($json);
        }
        return '';
    }
}
