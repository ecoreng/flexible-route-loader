<?php

namespace ecoreng\Test\Route;

class TestRouteMiddleware
{

    public function test($route)
    {
        
    }

    public function testWithParams($param1, $param2)
    {
        return function ($route) use ($param1, $param2) {
            
        };
    }

    public static function staticTest($route)
    {
        
    }

    public static function staticTestWithParams($param1, $param2)
    {
        return function ($route) use ($param1, $param2) {
            
        };
    }
}
