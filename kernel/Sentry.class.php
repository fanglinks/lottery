<?php
Class Sentry{
    public static $client;

    public static function autoload(){
        if(Config::SENTRY_DSN){
            require (ROOT . 'kernel/Raven/Autoloader.php');
            Raven_Autoloader::register();
            self::$client = new Raven_Client(Config::SENTRY_DSN);
            $error_handler = new Raven_ErrorHandler(self::$client);

            $error_handler->registerExceptionHandler();
            $error_handler->registerErrorHandler();
            $error_handler->registerShutdownFunction();

            // Register error handler callbacks
            /*
            set_error_handler(array($error_handler, 'handleError'));
            set_exception_handler(array($error_handler, 'handleException'));
            */
        }
    }
}
