<?php

namespace ecoreng\Route;

use ecoreng\Route\RouteConfigBagInterface as Bag;

class RouteLoaderMiddleware extends \Slim\Middleware
{

    /**
     * Instance of a class that implements RouteConfigBagInterface
     *
     * @var \ecoreng\Route\RouteConfigBagInterface
     */
    protected $bag;

    /**
     * String representing the service name in Slim's container where an instance of
     * a bag can be retrieved
     *
     * @var string
     */
    protected $bagName = 'route_config_bag';

    /**
     * Wheter or not to call "next" after the loader has done its job
     */
    protected $actAsMiddleware = true;

    /**
     * Construct
     *
     * @param mixed $bag
     */
    public function __construct($bagOrServiceName = null, $actAsMiddleware = true)
    {
        $this->actAsMiddleware = $actAsMiddleware;
        if ($bagOrServiceName instanceof Bag) {
            $this->bag = $bagOrServiceName;
        } elseif (is_string($bagOrServiceName)) {
            $this->bagName = $bagOrServiceName;
        }
    }

    /**
     * Set bag directly
     *
     * @param \ecoreng\Route\RouteConfigBagInterface $bag
     */
    public function setBag(Bag $bag)
    {
        $this->bag = $bag;
    }

    /**
     * Entry point for middleware
     */
    public function call()
    {

        if (!$this->bagReady()) {
            $this->getBagFromContainer();
        }
        $this->register();

        if ($this->actAsMiddleware) {
            $this->next->call();
        }
    }

    /**
     * Get bag from the app container using the passed service name
     *
     * @param string $bagServiceName
     * @throws \InvalidArgumentException
     */
    protected function getBagFromContainer()
    {
        $bagServiceName = $this->bagName;
        if (!$this->bagReady()) {
            try {
                if (class_exists('\\Slim\\App')) {
                    // Slim 3.0
                    $this->bag = $this->app[$bagServiceName];
                } else {
                    // Slim 2.*
                    $this->bag = $this->app->{$bagServiceName};
                    if (!$this->bagReady()) {
                        throw new \InvalidArgumentException;
                    }
                }
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Unresolvable service: ' . $bagServiceName);
            }

            if (!$this->bagReady()) {
                throw new \InvalidArgumentException(
                'Route config bag could not be retrieved from container using service name: ' . $bagServiceName
                );
            }
        }
    }

    /**
     * returns true is bag is setup and an instance of
     * \ecoreng\Route\RouteConfigBagInterface
     *
     * @return bool
     */
    protected function bagReady()
    {
        return $this->bag instanceof Bag;
    }

    /**
     * Register routes into the app using the route and group config from the bag
     */
    protected function register()
    {

        $this->nicknames = $this->bag->getNicknames();
        $routes = $this->bag->getRouteConfig();
        $groups = $this->bag->getGroupConfig();

        foreach (['routes' => $routes, 'groups' => $groups] as $routeType => $config) {
            switch ($routeType) {
                case 'groups':
                    foreach ($config as $name => $config) {
                        $this->setGroupRecursive($name, $config);
                    }
                    break;
                case 'routes':
                    foreach ($config as $name => $params) {
                        $this->setDefaultsAndMapRoute($params, $name);
                    }
                    break;
            }
        }
    }

    /**
     * Map a single route to $app using the specified parameters
     *
     * @param string $route
     * @param string $name
     * @param string $controller
     * @param string $methods
     * @param string $conditions
     */
    protected function mapRoute($route, $name, $controller = null, $methods = [], $conditions = [], $middleware = [])
    {
        $middleware = self::prepareMiddleware($middleware);
        $route = $this->app
                ->map($route, $controller)
                ->name($name)
                ->conditions($conditions);
        call_user_func_array(array($route, 'via'), $methods);
        if(count($middleware) > 0) {
            $route->setMiddleware($middleware);
        }
    }

    /**
     * Sets default parameters for a route config entry and maps that route
     *
     * @param array $config
     * @param string $name
     * @throws \BadMethodCallException
     */
    protected function setDefaultsAndMapRoute(array $config, $name)
    {
        $controller = $this->bag->getOrDefault('controller', $config, null);
        if ($controller === null) {
            throw new \BadMethodCallException('Missing controller parameter for route ' . $name);
        }
        $route = $this->bag->getOrDefault('route', $config, '/');
        $methods = explode('|', strtoupper($this->bag->getOrDefault('methods', $config, 'GET')));
        $conditions = $this->bag->getOrDefault('conditions', $config, []);
        $middleware = $this->bag->getOrDefault('middleware', $config, []);
        $this->mapRoute($route, $name, $controller, $methods, $conditions, $middleware);
    }

    /**
     * Sets a group and it's child(ren) routes and if it finds more levels, it uses
     * itself recursively to set more groups and routes
     *
     * @param string $name
     * @param array $config
     * @throws \BadMethodCallException
     */
    protected function setGroupRecursive($name, $config = [])
    {
        $app = $this->app;
        if (self::hasChildren($config)) {
            $children = $config['group'];
            $app->group($this->bag->getOrDefault('route', $config, '/'), function () use (&$app, &$children, &$name) {
                foreach ($children as $childName => $child) {
                    $this->setGroupRecursive($name . '_' . $childName, $child);
                }
            });
        } else {
            $this->setDefaultsAndMapRoute($config, $name);
        }
    }

    /**
     * Returns whether or not $config has children (checks for the key 'group')
     *
     * @param array $config
     * @return boolean
     */
    protected static function hasChildren($config = [])
    {
        if (array_key_exists('group', $config)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Processes and returns a Slim Route digestable array for middleware
     * 
     * @param array $middleware
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function prepareMiddleware(array $middleware)
    {
        if (count($middleware) === 0) {
            return [];
        }
        $readyMw = [];
        $app = $this->app;
        foreach ($middleware as $name => $config) {
            
            $params = $this->bag->getOrDefault('params', $config, []);
            if (array_key_exists('class', $config)) {
                $controller = $config['class'];
                if (strpos($controller, '::') !== false) {
                    // Static
                    if (!is_callable($controller)){
                        throw new \Exception('Function ' . $controller . ' is not callable');
                    }
                    if (count($params) > 0) {
                        $readyMw[] = call_user_func_array($controller, $params);
                    } else {
                        $readyMw[] = $controller;
                    }
                } elseif (strpos($controller, ':') !== false) {
                    // Regular method
                    $cparams = explode(":", $controller);
                     if (!is_callable([(new $cparams[0]), $cparams[1]])){
                        throw new \Exception('Function ' . $controller . ' is not callable');
                    }
                    if (count($params) > 0) {
                        $readyMw[] = call_user_func_array([(new $cparams[0]), $cparams[1]], $params);
                    } else {
                        $readyMw[] = [(new $cparams[0]), $cparams[1]];
                    }
                } else {
                    throw new \InvalidArgumentException(
                    'Controller ' . $controller . ' does not have a valid action; :: or : are '
                    . 'required to delimit the method'
                    );
                }
                continue;
            }
            if (array_key_exists('closure', $config)) {
                global $$config['closure'];
                if (!is_callable($$config['closure'])){
                    throw new \Exception('Function ' . $config['closure'] . ' is not callable');
                }
                if (count($params) > 0) {
                    $readyMw[] = call_user_func_array($$config['closure'], $params);
                } else {
                    $readyMw[] = $$config['closure'];
                }
                continue;
            }
            
            if (array_key_exists('function', $config)) {
                if (!is_callable($config['function'])){
                    throw new \Exception('Function ' . $config['function'] . ' is not callable');
                }
                if (count($params) > 0) {
                    $readyMw[] = call_user_func_array($config['function'], $params);
                } else {
                    $readyMw[] = $config['function'];
                }
                continue;
            }
            
            throw new \InvalidArgumentException('Middleware configuration lacking callable');
        }
        return $readyMw;
    }

}
