<?php

namespace ecoreng\Test\Route;

use \ecoreng\Route\RouteLoaderMiddleware as RLmw;

class RouteLoaderMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    protected $mw;

    public function setUp()
    {
        $this->validBag = new \ecoreng\Route\RouteConfigBag;

        if (class_exists('\\Slim\\App')) {
            $this->slimClassName = '\\Slim\\App';
            $this->slimVersion = 3;
        } else {
            $this->slimVersion = 2;
            $this->slimClassName = '\\Slim\\Slim';
        }
        
        // Route Configs

        $this->validRouteConfig = [
            'test' => [
                'controller' => '\\Example\\Controller\\ExampleController:action',
                'route' => '/sample-route/:placeholder1/:placeholder2',
                'methods' => 'GET|POST',
                'conditions' => [
                    'placeholder1' => '(19|20)\d\d',
                    'placeholder2' => '(20)\d\d',
                ]
            ],
            'test2' => [
                'controller' => '\\Example\\Controller\\ExampleController2:action2',
                'route' => '/sample-route2/:placeholder1/:placeholder2',
                'methods' => 'GET',
                'conditions' => [
                    'placeholder1' => '(19|20)\d\d',
                    'placeholder2' => '(20)\d\d',
                ]
            ]
        ];

        // Group Configs

        $this->validGroupConfig = [
            'api' => [
                'route' => '/api',
                'group' => [
                    'test-group' => [
                        'route' => '/test/:placeholder1/:placeholder2',
                        'methods' => 'GET|POST',
                        'controller' => '\\Example\\Controller\\ExampleController:action',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                    'test-group2' => [
                        'route' => '/test2',
                        'group' => [
                            'test-sub' => [
                                'route' => '/sub',
                                'methods' => 'POST',
                                'controller' => '\\Example\\Controller\\ExampleController2:action2',
                            ]
                        ]
                    ]
                ]
            ],
            'api2' => [
                'route' => '/api2',
                'group' => [
                    'test-group3' => [
                        'route' => '/test2/:placeholder1/:placeholder2',
                        'methods' => 'GET|POST',
                        'controller' => '\\Example\\Controller\\ExampleController3:action3',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                ]
            ]
        ];


        // nicknames

        $this->validNicknamesConfig = [
            'example' => '\\Company\\Example\\',
            'test' => '\\Company\\Example\\Test\\',
            'testanonymous' => function ($route) {
                return 'test';
            },
            'testclassnickname' => '\\ecoreng\\Test\\Route\\NicknameHandler:handler1'
        ];

        $this->validBag->setGroupConfig($this->validGroupConfig);
        $this->validBag->setRouteConfig($this->validRouteConfig);
        $this->validBag->setNicknames($this->validNicknamesConfig);
    }

    public function initMw($mw = null)
    {
        if ($mw === null) {
            $this->mw = $mw = new RLmw;
        }
        $this->app = new $this->slimClassName;
        $mw->setApplication($this->app);
        $mw->setNextMiddleware(new \ecoreng\Test\Route\NextTestMiddleware);
        return $mw;
    }

    public function addValidBag()
    {
        $this->mw->setBag($this->validBag);
    }
    
    public function getService($name)
    {
        if ($this->slimVersion == 3) {
            return $this->app[$name];
        } else {
            return $this->app->{$name};
        }
    }
    
    public function setService($name, $definition)
    {
        if ($this->slimVersion == 3) {
            $this->app[$name] = $definition;
        } else {
            $this->app->container->singleton($name, $definition);
        }
    }
    
    public function genericValidAsserts($router)
    {
        // Not testing controller or route because the Bag validates that already

        $this->assertEquals('/sample-route/:placeholder1/:placeholder2', $router->urlFor('test'));
        $nr = $router->getNamedRoute('test');
        $this->assertArrayHasKey('placeholder1', $nr->getConditions());
        $this->assertArrayHasKey('placeholder2', $nr->getConditions());
        $this->assertEquals(
            $this->validRouteConfig['test']['conditions']['placeholder1'],
            $nr->getConditions()['placeholder1']
        );
        $this->assertEquals(
            $this->validRouteConfig['test']['conditions']['placeholder2'],
            $nr->getConditions()['placeholder2']
        );
        $this->assertEquals(true, in_array('GET', $nr->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $nr->getHttpMethods()));
        // to do: route middleware test
        // var_dump($router->getNamedRoute('test')->getMiddleware());
        
        
        $this->assertEquals('/sample-route2/:placeholder1/:placeholder2', $router->urlFor('test2'));
        $nr = $router->getNamedRoute('test2');
        $this->assertArrayHasKey('placeholder1', $nr->getConditions());
        $this->assertArrayHasKey('placeholder2', $nr->getConditions());
        $this->assertEquals(
            $this->validRouteConfig['test2']['conditions']['placeholder1'],
            $nr->getConditions()['placeholder1']
        );
        $this->assertEquals(
            $this->validRouteConfig['test2']['conditions']['placeholder2'],
            $nr->getConditions()['placeholder2']
        );
        $this->assertEquals(true, in_array('GET', $nr->getHttpMethods()));

        
        $this->assertEquals('/api/test/:placeholder1/:placeholder2', $router->urlFor('api_test-group'));
        $nr = $router->getNamedRoute('api_test-group');
        $this->assertArrayHasKey('placeholder1', $nr->getConditions());
        $this->assertArrayHasKey('placeholder2', $nr->getConditions());
        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test-group']['conditions']['placeholder1'],
            $nr->getConditions()['placeholder1']
        );
        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test-group']['conditions']['placeholder2'],
            $nr->getConditions()['placeholder2']
        );
        $this->assertEquals(true, in_array('GET', $nr->getHttpMethods()));

        
        $this->assertEquals('/api/test2/sub', $router->urlFor('api_test-group2_test-sub'));
        $nr = $router->getNamedRoute('api_test-group2_test-sub');
        $this->assertEquals(true, in_array('POST', $nr->getHttpMethods()));

        
        $this->assertEquals('/api2/test2/:placeholder1/:placeholder2', $router->urlFor('api2_test-group3'));
        $nr = $router->getNamedRoute('api2_test-group3');
        $this->assertArrayHasKey('placeholder1', $nr->getConditions());
        $this->assertArrayHasKey('placeholder2', $nr->getConditions());
        $this->assertEquals(
            $this->validGroupConfig['api2']['group']['test-group3']['conditions']['placeholder1'],
            $nr->getConditions()['placeholder1']
        );
        $this->assertEquals(
            $this->validGroupConfig['api2']['group']['test-group3']['conditions']['placeholder2'],
            $nr->getConditions()['placeholder2']
        );
        $this->assertEquals(true, in_array('GET', $nr->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $nr->getHttpMethods()));
        
    }

    public function testConstructWithBag()
    {
        $mw = new RLmw($this->validBag);
        $mw = $this->initMw($mw);
        $mw->call();
        $router = $this->getService('router');
        $this->genericValidAsserts($router);
    }
    
    public function testWithBagSetter()
    {
        $this->initMw();
        $this->addValidBag();
        $this->mw->call();
        $router = $this->getService('router');
        $this->genericValidAsserts($router);
    }

    public function testGetBagFromContainerWithDefaultName()
    {
        $this->initMw();
        
        $this->setService('route_config_bag', function ($c = null) {
            return new \ecoreng\Route\RouteConfigBag;
        });
        
        $bag = $this->getService('route_config_bag');
        $bag->setGroupConfig($this->validGroupConfig);
        $bag->setRouteConfig($this->validRouteConfig);
        $bag->setNicknames($this->validNicknamesConfig);

        $this->mw->call();
        
        $router = $this->getService('router');
        $this->genericValidAsserts($router);
    }

    public function testGetBagFromContainerConstructName()
    {
        $this->mw = new RLmw('_route_config_bag');
        $this->app = new $this->slimClassName;
        $this->mw->setApplication($this->app);
        $this->mw->setNextMiddleware(new \ecoreng\Test\Route\NextTestMiddleware);

        $this->setService('_route_config_bag', function ($c = null) {
            return new \ecoreng\Route\RouteConfigBag;
        });

        $bag = $this->getService('_route_config_bag');
        $bag->setGroupConfig($this->validGroupConfig);
        $bag->setRouteConfig($this->validRouteConfig);
        $bag->setNicknames($this->validNicknamesConfig);
        $this->mw->call();
        $router = $this->getService('router');
        $this->genericValidAsserts($router);
    }

    public function testGetUnsetBagFromContainerFail()
    {
        $this->mw = new RLmw('route_config_bag');
        $this->app = new $this->slimClassName;
        $this->mw->setApplication($this->app);
        $this->mw->setNextMiddleware(new \ecoreng\Test\Route\NextTestMiddleware);
        try {
            $this->mw->call();
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }
    
    public function testNotActingAsMiddleware()
    {
        $nmw = new RLmw($this->validBag, false);
        $this->app = new $this->slimClassName;
        $nmw->setApplication($this->app);
        // Missing "next middleware"
        $nmw->call();
        $router = $this->getService('router');
        $this->genericValidAsserts($router);
    }
}
