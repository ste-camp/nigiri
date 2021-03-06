<?php
use nigiri\Psr4AutoloaderClass;
use nigiri\Site;

ini_set('display_errors', false);
ini_set('log_errors', true);
ini_set('error_log', __DIR__.'/nigiri_error.log');

require_once __DIR__.'/includes/defines.php';
require_once __DIR__.'/classes/class_loader.php';
require_once __DIR__.'/includes/functions.php';

set_error_handler('error_to_exception_handler', E_ALL);
set_exception_handler('uncaught_exception_handler');
register_shutdown_function('fatal_error_handler');

//Compatibilità con composer
if(file_exists(__DIR__.'/vendor/autoload.php')){
    require_once __DIR__.'/vendor/autoload.php';
}

$autoloader = new Psr4AutoloaderClass();
$autoloader->register();
$autoloader->addNamespace('nigiri', __DIR__.'/classes');
$autoloader->addNamespace('site', __DIR__);

$config = require_once __DIR__.'/includes/settings.php';

$config['autoloader'] = $autoloader;

Site::init($config);

/**
 * This needs to be a statement on its own. If you call it inside theme->append(), any theme change happening inside the
 * controller's action won't make any real effect
 */
$output = Site::getRouter()->routeRequest();

Site::getTheme()->append($output);

Site::printPage();
