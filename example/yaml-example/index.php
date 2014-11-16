<?php


// Autoloader from composer
$autoloader = require_once('../../vendor/autoload.php');
$autoloader->addPsr4('ExampleCo\\Example\\', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// Slim Api Version [2|3]
$slimVersion = 2;

// Instantiation of slim
$slimConfig = ['debug' => true];
if ($slimVersion === 2) {
    // Slim 2.*
    $app = new \Slim\Slim($slimConfig);
} else {
    // Slim 3.*
    $app = new \Slim\App($slimConfig);
}

// Remember to add this to your composer.json:
// ..
// require: {
//  ..
//      "symfony/yaml": "2.5.6"
//  ..
// }
use Symfony\Component\Yaml\Parser;

// Instantiate a new Yaml Parser and load the routes.yml file as an array
$yaml = new Parser();
$yamlContent = file_get_contents('routes.yml');
$config = $yaml->parse($yamlContent);

// define the route loader as a service in the container
if ($slimVersion === 2) {
    // Slim 2.*
    $app->container->singleton('RouteLoader', function () use ($app) {
        return new \ecoreng\Route\Loader($app);
    });
} else {
    // Slim 3.*
    $app['RouteLoader'] = function (\Pimple\Container $c) {
        return new RouteLoader($c);
    };
}

// add groups and routes
$app->RouteLoader->addRoutes($config['routes']);
$app->RouteLoader->addGroups($config['groups']);


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
