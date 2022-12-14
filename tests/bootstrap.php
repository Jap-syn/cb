<?php

chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
//Zend\Mvc\Application::init(require 'config/application.config.php')->run();

/*
// Setup the path related constants.
define ( 'DS', DIRECTORY_SEPARATOR );
require dirname ( __DIR__ ) . DS . 'init_autoloader.php';
//require dirname ( __DIR__ ) . DS . 'vendor' . DS . 'autoload.php';
*/