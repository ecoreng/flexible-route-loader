Flexible Route Loader
=======================
[![Build Status](https://travis-ci.org/ecoreng/flexible-route-loader.svg?branch=master)](https://travis-ci.org/ecoreng/flexible-route-loader) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/0f7d4aa7-f480-46dc-b9dc-a6191bfab8f5/mini.png)](https://insight.sensiolabs.com/projects/0f7d4aa7-f480-46dc-b9dc-a6191bfab8f5)

A route loader for Slim.

Notice that this is NOT a ROUTER, this just loads routes into the default Slim router.

It gives you the flexibility to configure your routes using an array. It supports
Groups, Named Routes, Multiple or Single Http methods and conditions. At the moment
it's only possible to route using "Controller" classes, not anonymous functions.


## Why is this useful? ##

Because you can manage your routes externally, as a separate file (i.e. Yaml, Json, PHP array, etc) without
having to edit your front controller.

Check the ``Examples`` Section at the bottom for example on how to load your routes from Yaml and/or Json Files


## Usage ##
```
// Slim 2.*
$app = new \Slim\Slim;

$loader = new ecoreng\Route\Loader($app);
$loader->addRoutes([
    'test-route' => [
            'controller' => '\\Vendor\\Package\\Controller:testAction',
            'route' => '/test',
            'methods' => 'GET|POST'
        ]
    ]
);
$loader->addGroups([
        'pre-name' => [
            'route' => '/api',
            'group' => [
                'test' => [
                    'route' => '/test'
                    'controller' => '\\ExampleCo\\Api\\ApiController:test2Action',
                    'methods' => 'GET'
                ],
                'test2 => [
                    'route' => '/v1',
                    'group' => [
                        'deep-route' => [
                            'route' => '/client/:id',
                            'controller' => '\\ExampleCo\\Api\\ApiController:getClientAction',
                            'methods' => 'GET|POST|PUT',
                            'conditions' => [
                                'id' => '\d?\d?\d'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
);

$app->run();

```
This will set 3 routes:
 - route named ``test-route``, accesible through ``/test``, proccessed by ``\\ExampleCo\\Api\\ApiController:test2Action``, via GET or POST
 - route named ``pre-name_test``, accesible through ``/api/test``, processed by ``\\ExampleCo\\Api\\ApiController:test2Action``, via GET
 - route named ``pre-name_test2_deep-route``, accesible through ``/api/client/:id``, processed by ``\\ExampleCo\\Api\\ApiController:getClientAction``, via GET or POST or PUT

Notice the name inheritance from parent plus underscore in route group names


### Usage with Container ###

```
// Slim 2.*
$app = new \Slim\Slim;

// Slim 2.*
$app->container->singleton('route_loader', function () {
    return new ecoreng\Route\Loader;
});

// Slim 2.*
$loader = $app->route_loader;

$loader->addRoutes([
    'test-route' => [
            'controller' => '\\Vendor\\Package\\Controller:testAction',
            'route' => '/test',
            'methods' => 'GET|POST'
        ]
    ]
);

$app->run();
```

## To Do ##
- Add Controller Class "nickname" replacement and processing (e.g.: @CoEx turns into \Company\Example\Controller)
- Add tests for middleware

## Examples ##
Be sure to check out the example:

- Read a full example using a Json File @ ``example\json-example\index.php``;
- Read a full example using a Yaml File @ ``example\yaml-example\index.php``;

## Contribute ##
Pull Requests welcome, add some tests if necessary and adhere to PSR-2 coding style.
