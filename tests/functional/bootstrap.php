<?php
set_error_handler(function($code, $message){
    throw new \ErrorException($message, $code);
});

require __DIR__ . "/../../vendor/autoload.php";
