<?php

namespace ExampleCo\Example;

class ExampleRouteMiddleware
{

    public function __construct()
    {
        echo 'ExampleRouteMiddleware instantiated (should not happen)';
    }

    /**
     * For Slim 3.* you should typehint for \Slim\Interfaces\RouteInterface
     * @param \Slim\Route $route
     */
    public function a12n(\Slim\Route $route)
    {
        echo '<br><br>Route Middleware in action: a12n { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '}<br>';
    }

    /**
     * For Slim 3.* you should typehint for \Slim\Interfaces\RouteInterface
     * @param \Slim\Route $route
     */
    public static function other(\Slim\Route $route)
    {
        echo '<br>Route Middleware in action: other { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '}<br>';
    }
}
