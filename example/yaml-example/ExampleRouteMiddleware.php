<?php

namespace ExampleCo\Example;

class ExampleRouteMiddleware
{

    public function __construct()
    {
        echo 'ExampleRouteMiddleware instantiated (should not happen)';
    }

    public function a12n($test1, $test2)
    {
        return function ($route) use ($test1, $test2) {
            echo '<br><br>Route Middleware in action: a12n { <br>';
            echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
            echo '&nbsp;&nbsp;$test1: ' . $test1 . '<br>';
            echo '&nbsp;&nbsp;$test2: ' . $test2 . '<br>';
            echo '}<br>';
        };
    }

    public static function other($test5, $test6)
    {
        return function ($route) use ($test5, $test6) {
            echo '<br>Route Middleware in action: other { <br>';
            echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
            echo '&nbsp;&nbsp;$test5: ' . $test5 . '<br>';
            echo '&nbsp;&nbsp;$test6: ' . $test6 . '<br>';
            echo '}<br>';
        };
    }
}
