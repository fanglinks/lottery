<?php
if(Platform::$alipay ||  (defined('PAGESELF') && preg_match('/alipay/i', PAGESELF )) ){
    require ROOT.'module/Alipay/function.inc.php';
    alipay_register();
}

function ui(){
    if(isset($_SERVER['SCRIPT_NAME']) && preg_match('/\/([a-z0-9]+)\//i', $_SERVER['SCRIPT_NAME'], $matches)){
        if(isset($matches[1])){
            return ROOT . $matches[1] . '/front/ui/';
        }
    }
    return UI;
}

DB::setBait('read',Config::getSlaveDBconfig());
DB::setBait('martin',Config::getMartinDBconfig());
DB::setHarpoon('master');
