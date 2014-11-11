<?php

namespace ecoreng\Test\Route;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $appClassName = class_exists('\\Slim\\App') ? '\\Slim\\App' : '\\Slim\\Slim';
        $this->app = new $appClassName;
        $this->loader = new \ecoreng\Route\Loader($this->app);

        $this->invalidNotMultidimensional = ['test', 'test2'];

        // Route Configs

        $this->validRouteConfig = ['test' => [
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

        $this->invalidRouteMissingController = ['test' => [
                'route' => '/sample-route/:placeholder1/:placeholder2',
                'methods' => 'GET|POST',
                'conditions' => [
                    'placeholder1' => '(19|20)\d\d',
                    'placeholder2' => '(20)\d\d',
                ]
        ]];

        $this->invalidRouteMissingRoute = ['test' => [
                'controller' => '\\Example\\Controller\\ExampleController2:action2',
                'methods' => 'GET|POST',
                'conditions' => [
                    'placeholder1' => '(19|20)\d\d',
                    'placeholder2' => '(20)\d\d',
                ]
        ]];

        // Group Configs

        $this->validGroupConfig = [
            'api' => [
                'route' => '/api',
                'group' => [
                    'test1' => [
                        'route' => '/test',
                        'methods' => 'GET|POST',
                        'controller' => '\\Example\\Controller\\ExampleController:action',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                    'test2' => [
                        'route' => '/test2',
                        'group' => [
                            'test_sub' => [
                                'route' => '/sub',
                                'methods' => 'POST',
                                'controller' => '\\Example\\Controller\\ExampleController2:action2',
                                'conditions' => [
                                    'placeholder1' => '(20)\d\d',
                                    'placeholder2' => '(19|20)\d\d',
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'api2' => [
                'route' => '/api2',
                'group' => [
                    'test12' => [
                        'route' => '/test2',
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

        $this->invalidGroupMissingControllerDeep = [
            'api' => [
                'route' => '/api',
                'group' => [
                    'test1' => [
                        'route' => '/test',
                        'methods' => 'GET|POST',
                        'controller' => '\\Example\\Controller\\ExampleController:action',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                    'test2' => [
                        'route' => '/test2',
                        'group' => [
                            'test_sub' => [
                                'route' => '/sub',
                                'methods' => 'POST',
                                'conditions' => [
                                    'placeholder1' => '(20)\d\d',
                                    'placeholder2' => '(19|20)\d\d',
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->invalidGroupMissingController = [
            'api' => [
                'route' => '/api',
                'group' => [
                    'test1' => [
                        'route' => '/test',
                        'methods' => 'GET|POST',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                    'test2' => [
                        'route' => '/test2',
                        'group' => [
                            'test_sub' => [
                                'controller' => '\\Example\\Controller\\ExampleController2:action2',
                                'route' => '/sub',
                                'methods' => 'POST',
                                'conditions' => [
                                    'placeholder1' => '(20)\d\d',
                                    'placeholder2' => '(19|20)\d\d',
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->invalidGroupMissingRoute = [
            'api' => [
                'group' => [
                    'test1' => [
                        'methods' => 'GET|POST',
                        'controller' => '\\Example\\Controller\\ExampleController:action',
                        'conditions' => [
                            'placeholder1' => '(19|20)\d\d',
                            'placeholder2' => '(20)\d\d',
                        ],
                    ],
                ]
            ]
        ];

        $this->invalidGroupMissingGroupAndController = [
            'api' => [
                'route' => '/test2'
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

        $this->invalidNicknamesMultidimensional = [
            'example' => []
        ];

        $this->invalidNicknamesEmptyReplacement = [
            'example' => ''
        ];

        $this->invalidNicknamesNotAssocArray = ['example'];
    }

    public function testSetRouteConfigValid()
    {

        $this->loader->addRoutes($this->validRouteConfig);

        $test = $this->app->router->getNamedRoute('test');
        $test2 = $this->app->router->getNamedRoute('test2');

        $this->assertEquals('/sample-route/:placeholder1/:placeholder2', $test->getPattern());
        $this->assertEquals('/sample-route2/:placeholder1/:placeholder2', $test2->getPattern());

        $this->assertEquals(true, in_array('GET', $test->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $test->getHttpMethods()));
        $this->assertEquals(true, in_array('GET', $test2->getHttpMethods()));

        $this->assertEquals(true, array_key_exists('placeholder1', $test->getConditions()));
        $this->assertEquals(true, array_key_exists('placeholder2', $test->getConditions()));
        $this->assertEquals('(19|20)\d\d', $test->getConditions()['placeholder1']);
        $this->assertEquals('(20)\d\d', $test->getConditions()['placeholder2']);

        $this->assertEquals(true, array_key_exists('placeholder1', $test2->getConditions()));
        $this->assertEquals(true, array_key_exists('placeholder2', $test2->getConditions()));
        $this->assertEquals('(19|20)\d\d', $test2->getConditions()['placeholder1']);
        $this->assertEquals('(20)\d\d', $test2->getConditions()['placeholder2']);
    }

    public function testInvalidRouteMultidimensional()
    {
        try {
            $this->loader->addRoutes($this->invalidNotMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidMissingController()
    {
        try {
            $this->loader->addRoutes($this->invalidRouteMissingController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidMissingRoute()
    {
        try {
            $this->loader->addRoutes($this->invalidRouteMissingRoute);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testSetGroupConfigValid()
    {
        $this->loader->addGroups($this->validGroupConfig);

        $test1 = $this->app->router->getNamedRoute('api_test1');
        $test2 = $this->app->router->getNamedRoute('api_test2_test_sub');
        $test3 = $this->app->router->getNamedRoute('api2_test12');

        $this->assertEquals('/api/test', $test1->getPattern());
        $this->assertEquals('/api/test2/sub', $test2->getPattern());
        $this->assertEquals('/api2/test2', $test3->getPattern());

        $this->assertEquals(true, in_array('GET', $test1->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $test1->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $test2->getHttpMethods()));
        $this->assertEquals(true, in_array('GET', $test3->getHttpMethods()));
        $this->assertEquals(true, in_array('POST', $test3->getHttpMethods()));

        $this->assertEquals(true, array_key_exists('placeholder1', $test1->getConditions()));
        $this->assertEquals(true, array_key_exists('placeholder2', $test1->getConditions()));
        $this->assertEquals('(19|20)\d\d', $test1->getConditions()['placeholder1']);
        $this->assertEquals('(20)\d\d', $test1->getConditions()['placeholder2']);
        
        $this->assertEquals(true, array_key_exists('placeholder1', $test2->getConditions()));
        $this->assertEquals(true, array_key_exists('placeholder2', $test2->getConditions()));
        $this->assertEquals('(20)\d\d', $test2->getConditions()['placeholder1']);
        $this->assertEquals('(19|20)\d\d', $test2->getConditions()['placeholder2']);
        
        $this->assertEquals(true, array_key_exists('placeholder1', $test3->getConditions()));
        $this->assertEquals(true, array_key_exists('placeholder2', $test3->getConditions()));
        $this->assertEquals('(19|20)\d\d', $test3->getConditions()['placeholder1']);
        $this->assertEquals('(20)\d\d', $test3->getConditions()['placeholder2']);
    }

    public function testInvalidGroupMultidimensional()
    {
        try {
            $this->loader->addGroups($this->invalidNotMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingControllerDeep()
    {
        try {
            $this->loader->addGroups($this->invalidGroupMissingControllerDeep);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingController()
    {
        try {
            $this->loader->addGroups($this->invalidGroupMissingController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingRoute()
    {
        try {
            $this->loader->addGroups($this->invalidGroupMissingRoute);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingGroupAndController()
    {
        try {
            $this->loader->addGroups($this->invalidGroupMissingGroupAndController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testSetNicknamesValid()
    {
        $this->loader->addNicknames($this->validNicknamesConfig);
        $returningNicknamesConfig = $this->loader->getNicknames();
    }

    public function testInvalidNicknamesMultidimensional()
    {
        try {
            $this->loader->addNicknames($this->invalidNicknamesMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidNicknamesEmptyReplacement()
    {
        try {
            $this->loader->addNicknames($this->invalidNicknamesEmptyReplacement);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidNicknamesNotAssocArray()
    {
        try {
            $this->loader->addNicknames($this->invalidNicknamesNotAssocArray);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }
}
