<?php

namespace ExampleCo\Example;

class ExampleRouteMiddleware
{

    public function __construct()
    {
        echo 'ExampleRouteMiddleware instantiated (should not happen)';
    }

    public function a12n($route)
    {
        echo '<br><br>Route Middleware in action: a12n { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '}<br>';
    }

    public static function other($route)
    {
        echo '<br>Route Middleware in action: other { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '}<br>';
    }
}
