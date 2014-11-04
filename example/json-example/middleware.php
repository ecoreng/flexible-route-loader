<?php

// Route Middleware as Function
function test($route)
{
        echo '<br>Route Middleware in action: test (function) { <br>';
        echo '&nbsp;&nbsp;analizing route: ' . $route->getName() . '<br>';
        echo '}<br>';
}
