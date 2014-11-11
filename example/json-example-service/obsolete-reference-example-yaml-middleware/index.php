<?php
// Route Middleware as Closure
$closureMiddleware = function ($test3, $test4) {
    return function ($route) use ($test3, $test4) {
        echo '<br>Route Middleware in action: closureMiddleware { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '&nbsp;&nbsp;$test3: ' . $test3 . '<br>';
        echo '&nbsp;&nbsp;$test4: ' . $test4 . '<br>';
        echo '}<br>';
    };
};
require_once('middleware.php');

// Autoloader from composer
require_once('../../vendor/autoload.php');

// require our controller and route middleware (use an autoloader in the real setup)
require_once('ExampleController.php');
require_once('ExampleRouteMiddleware.php');

// Use our RouteLoader and Bag
use ecoreng\Route\RouteConfigBag as RouteBag;
use ecoreng\Route\RouteLoaderMiddleware as RouteLoader;

// Remember to add this to your composer:
// ..
// require: {
//  ..
//      "symfony/yaml": "2.5.6"
//  ..
// }
use Symfony\Component\Yaml\Parser;

// Instantiation of slim
$slimConfig = ['debug' => true];
$app = new \Slim\Slim($slimConfig);   // Slim 2.*
// $app = new \Slim\App($slimConfig); // Slim 3.*

// Instantiate a new Yaml Parser and load the routes.yml file as an array
$yaml = new Parser();
$yamlContent = file_get_contents('routes.yml');
$config = $yaml->parse($yamlContent);

// Instantiate a new RouteConfigBag [this holds and validates our route config until it's time to load]
$bag = new RouteBag;
// Set groups and routes in the bag
$bag->setRouteConfig($config['routes']);
$bag->setGroupConfig($config['groups']);

// Add the RouteLoaderMiddleware to Slim with the previously set up bag as a dependency in the constructor
$app->add(new RouteLoader($bag));

// Define the generic landing page with links (optional)
$app->get('/', function () use ($yamlContent) {
    echo <<<EOT
    <h1>These Urls are loaded from the Yaml File:</h1>
    <a href="index.php/test">Regular Url</a><br>
    <a href="index.php/api/test">Group Url</a><br>
    <a href="index.php/api/test2/test-sub">Nested Group Url</a><br>
    <br><br>
    (the current page is configured as a regular Slim anonymous function @ index.php)
    <h2>Yaml file content</h2>
    <pre style="border:1px solid #000;border-radius:0.3em;margin:1em;
        background:#f0f0f0;padding:1em;display:inline-block;">
{$yamlContent}
    </pre>
EOT;
});

// Call all middleware & the app
$app->run();
