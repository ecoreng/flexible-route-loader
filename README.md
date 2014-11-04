Flexible Route Loader Middleware
=======================
[![Build Status](https://travis-ci.org/ecoreng/FlexibleRouteLoader.svg)](https://travis-ci.org/ecoreng/FlexibleRouteLoader) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/0f7d4aa7-f480-46dc-b9dc-a6191bfab8f5/mini.png)](https://insight.sensiolabs.com/projects/0f7d4aa7-f480-46dc-b9dc-a6191bfab8f5)

A route loader for Slim.

Notice that this is NOT a ROUTER, this just loads routes into the default Slim router.

It gives you the flexibility to configure your routes using an array. It supports
Groups, Named Routes, Multiple or Single Http methods and conditions. At the moment
it'ss only possible to route using "Controller" classes, not anonymous functions.


## Why is this useful? ##

Because you can manage your routes externally, as a separate file (i.e. Yaml, Json, PHP array, etc) without
having to edit your front controller.

Check the ``Examples`` Section at the bottom for example on how to load your routes from Yaml and/or Json Files


## Usage ##
```
// Slim 2.*
$app = new \Slim\Slim;

$bag = new ecoreng\Route\RouteConfigBag;
$bag->setRouteConfig([
    'test-route' => [
            'controller' => '\\Vendor\\Package\\Controller:testAction',
            'route' => '/test',
            'methods' => 'GET|POST'
        ]
    ]
);
$bag->setGroupConfig([
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

$app->add(new ecoreng\Route\RouteLoaderMiddleware($bag));

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
$app->container->singleton('route_bag', function () {
    return new ecoreng\Route\RouteConfigBag;
});

// Slim 2.*
$bag = $app->route_bag;

$bag->setRouteConfig([
    'test-route' => [
            'controller' => '\\Vendor\\Package\\Controller:testAction',
            'route' => '/test',
            'methods' => 'GET|POST'
        ]
    ]
);

$app->add(new ecoreng\Route\RouteLoaderMiddleware('route_bag'));

$app->run();
```
You can just pass the service name in the Middleware constructor and the route loader will retrieve the route bag from
the container automatically.


## Known Issues ##
Due to the nature of Middleware, the RouteLoader is not able to load the routes
until it's called, and that's after ``$app->run()``, so you won't be able to get
routes defined in the bag before ``$app->run()`` using ``$app->urlFor()`` or any
other mean (those routes won't be in the router until after ``$app->run()``)

To bypass this limitation and load your routes before ``$app->run()`` you are going to have
to use this Middleware as a regular service with some manual dependency injection:

```
// Slim 2.*
$app = new \Slim\Slim;

$bag = new \ecoreng\Route\RouteConfigBag;
$bag->setRouteConfig([
    'test-route' => [
            'controller' => '\\Vendor\\Package\\Controller:testAction',
            'route' => '/test',
            'methods' => 'GET|POST'
        ]
    ]
);
// last parameter should be false, ($actAsMiddleware = false)
$RouteLoader = new \ecoreng\Route\RouteLoaderMiddleware($bag, false);

// Manually load the $app dependency
$RouteLoader->setApplication($app);

// Manually init the process
$RouteLoader->call();

// Now you can do this:
echo $app->urlFor('test-route'); // prints: "/test"

$app->run();
```

## To Do ##
- Add Controller Class "nickname" replacement and processing (e.g.: @CoEx turns into \Company\Example\Controller)


## Examples ##
Be sure to check out both example as they have set up route middleware differently:

- Read a full example using Symfony Yaml Component @ ``example\yaml-example\index.php``; this example uses Route Middleware directly (that only take one parameter (``Route`` Object));
- Read a full example using a Json File @ ``example\json-example\index.php``; this example uses Route Middleware that take parameters on call and return anonymous functions that take one parameter (``Route`` Object) to be called later

## Contribute ##
Pull Requests welcome, add some tests if necessary and adhere to PSR-2 coding style.
