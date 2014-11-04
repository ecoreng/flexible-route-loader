<?php

error_reporting(-1);
date_default_timezone_set('America/Los_Angeles');
/**
 * Path trickery ensures test suite will always run, standalone or within
 * another composer package. Designed to find composer autoloader and require
 */
$vendorPos = strpos(__DIR__, 'vendor/ecoreng/Service/');
if ($vendorPos !== false) {
    // Package has been cloned within another composer package, resolve path to autoloader
    $vendorDir = substr(__DIR__, 0, $vendorPos) . 'vendor/';
    $loader = require $vendorDir . 'autoload.php';
} else {
    // Package itself (cloned standalone)
    $loader = require __DIR__ . '/../vendor/autoload.php';
}

$loader->setPsr4('ecoreng\\Test\\', __DIR__ . '/ecoreng/');

require('ecoreng/Route/GenericMiddleware.php');


// Define some generic middleware
$closureWithParams = function ($param4) {
    return function ($route) use ($param4) {
        
    };
};
$closureNoParams = function ($route) {
    
};

return $loader;
