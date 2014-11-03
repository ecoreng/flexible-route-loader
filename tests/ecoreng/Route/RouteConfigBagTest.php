<?php

namespace ecoreng\Test\Route;

class RouteConfigBagTest extends \PHPUnit_Framework_TestCase
{

    protected $bag;

    public function setUp()
    {
        $this->bag = new \ecoreng\Route\RouteConfigBag;

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
                'route' => '/sample-route/:placeholder1/:placeholder2',
                'methods' => 'GET|POST',
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

        $this->bag->setRouteConfig($this->validRouteConfig);
        $returningRouteConfig = $this->bag->getRouteConfig();

        $this->assertArrayHasKey('test', $returningRouteConfig);
        $this->assertArrayHasKey('test2', $returningRouteConfig);

        $this->assertArrayHasKey('controller', $returningRouteConfig['test']);
        $this->assertArrayHasKey('route', $returningRouteConfig['test']);
        $this->assertArrayHasKey('methods', $returningRouteConfig['test']);
        $this->assertArrayHasKey('conditions', $returningRouteConfig['test']);

        $this->assertEquals($this->validRouteConfig['test']['controller'], $returningRouteConfig['test']['controller']);
        $this->assertEquals($this->validRouteConfig['test']['route'], $returningRouteConfig['test']['route']);
        $this->assertEquals($this->validRouteConfig['test']['methods'], $returningRouteConfig['test']['methods']);
        $this->assertEquals(true, is_array($returningRouteConfig['test']['conditions']));

        $this->assertArrayHasKey('placeholder1', $returningRouteConfig['test']['conditions']);
        $this->assertArrayHasKey('placeholder2', $returningRouteConfig['test']['conditions']);

        $this->assertEquals(
            $this->validRouteConfig['test']['conditions']['placeholder1'],
            $returningRouteConfig['test']['conditions']['placeholder1']
        );
        $this->assertEquals(
            $this->validRouteConfig['test']['conditions']['placeholder2'],
            $returningRouteConfig['test']['conditions']['placeholder2']
        );
    }

    public function testInvalidRouteMultidimensional()
    {
        try {
            $this->bag->setRouteConfig($this->invalidNotMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidMissingController()
    {
        try {
            $this->bag->setRouteConfig($this->invalidRouteMissingController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidMissingRoute()
    {
        try {
            $this->bag->setRouteConfig($this->invalidRouteMissingRoute);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testSetGroupConfigValid()
    {

        $this->bag->setGroupConfig($this->validGroupConfig);
        $returningGroupConfig = $this->bag->getGroupConfig();

        $this->assertArrayHasKey('api', $returningGroupConfig);
        $this->assertArrayHasKey('api2', $returningGroupConfig);

        $this->assertArrayHasKey('group', $returningGroupConfig['api']);
        $this->assertArrayHasKey('route', $returningGroupConfig['api']);

        $this->assertEquals($this->validGroupConfig['api']['route'], $returningGroupConfig['api']['route']);
        $this->assertEquals(true, is_array($returningGroupConfig['api']['group']));

        $this->assertArrayHasKey('test1', $returningGroupConfig['api']['group']);
        $this->assertArrayHasKey('test2', $returningGroupConfig['api']['group']);

        $this->assertArrayHasKey('methods', $returningGroupConfig['api']['group']['test1']);
        $this->assertArrayHasKey('route', $returningGroupConfig['api']['group']['test1']);
        $this->assertArrayHasKey('controller', $returningGroupConfig['api']['group']['test1']);
        $this->assertArrayHasKey('conditions', $returningGroupConfig['api']['group']['test1']);

        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test1']['methods'],
            $returningGroupConfig['api']['group']['test1']['methods']
        );
        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test1']['route'],
            $returningGroupConfig['api']['group']['test1']['route']
        );
        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test1']['controller'],
            $returningGroupConfig['api']['group']['test1']['controller']
        );
        $this->assertEquals(true, is_array($returningGroupConfig['api']['group']['test1']['conditions']));

        $this->assertArrayHasKey('placeholder1', $returningGroupConfig['api']['group']['test1']['conditions']);
        $this->assertArrayHasKey('placeholder2', $returningGroupConfig['api']['group']['test1']['conditions']);

        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test1']['conditions']['placeholder1'],
            $returningGroupConfig['api']['group']['test1']['conditions']['placeholder1']
        );
        $this->assertEquals(
            $this->validGroupConfig['api']['group']['test1']['conditions']['placeholder2'],
            $returningGroupConfig['api']['group']['test1']['conditions']['placeholder2']
        );
    }

    public function testInvalidGroupMultidimensional()
    {
        try {
            $this->bag->setGroupConfig($this->invalidNotMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingControllerDeep()
    {
        try {
            $this->bag->setGroupConfig($this->invalidGroupMissingControllerDeep);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingController()
    {
        try {
            $this->bag->setGroupConfig($this->invalidGroupMissingController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingRoute()
    {
        try {
            $this->bag->setGroupConfig($this->invalidGroupMissingRoute);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidGroupMissingGroupAndController()
    {
        try {
            $this->bag->setGroupConfig($this->invalidGroupMissingGroupAndController);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testSetNicknamesValid()
    {
        $this->bag->setNicknames($this->validNicknamesConfig);
        $returningNicknamesConfig = $this->bag->getNicknames();
    }

    public function testInvalidNicknamesMultidimensional()
    {
        try {
            $this->bag->setNicknames($this->invalidNicknamesMultidimensional);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidNicknamesEmptyReplacement()
    {
        try {
            $this->bag->setNicknames($this->invalidNicknamesEmptyReplacement);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }

    public function testInvalidNicknamesNotAssocArray()
    {
        try {
            $this->bag->setNicknames($this->invalidNicknamesNotAssocArray);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e);
        }
    }
}
