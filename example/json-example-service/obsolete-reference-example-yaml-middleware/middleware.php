<?php
function test($test7, $test8)
{
    return function ($route) use ($test7, $test8) {
        echo '<br>Route Middleware in action: test (function) { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '&nbsp;&nbsp;$test3: ' . $test7 . '<br>';
        echo '&nbsp;&nbsp;$test4: ' . $test8 . '<br>';
        echo '}<br>';
    };
}
