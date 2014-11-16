<?php

namespace ecoreng\Route;

class Loader
{

    protected $app;
    protected $slimApi = 2;
    protected $nicknames;

    public function __construct($app)
    {
        $appClassName = class_exists('\\Slim\\App') ? '\\Slim\\App' : '\\Slim\\Slim';
        if ($app instanceof $appClassName) {
            $this->app = $app;
            if (stripos($appClassName, 'app') !== false) {
                $this->slimApi = 3;
            }
        } else {
            throw new \Exception('$app expects an instance of the Slim App');
        }
    }

    public function addGroups(array $config)
    {
        if (self::groupConfigValid($config)) {
            foreach ($config as $name => $config) {
                $this->setGroupRecursive($name, $config);
            }
        } else {
            throw new \InvalidArgumentException('config array is not a valid group config array');
        }
    }

    public function addRoutes(array $config)
    {
        if (self::routeConfigValid($config)) {
            foreach ($config as $name => $params) {
                $this->setDefaultsAndMapRoute($params, $name);
            }
        } else {
            throw new \InvalidArgumentException('config array is not a valid route config array');
        }
    }

    public function addNicknames(array $config)
    {
        if (self::nicknamesValid($config)) {
            $this->mergeConfigs('nicknames', $config);
        } else {
            throw new \InvalidArgumentException('nicknames array is not a valid nicknames array');
        }
    }

    public function getNicknames()
    {
        return $this->nicknames;
    }

    /**
     * Gets $key from $haystack if it exists, otherwise return $default
     *
     * @return mixed
     */
    public function getOrDefault($key, array $haystack, $default)
    {
        return array_key_exists($key, $haystack) ? $haystack[$key] : $default;
    }

    /**
     * Sets or merges config arrays depending on whether or not it's been set before
     */
    protected function mergeConfigs($config, $newConfig)
    {
        if ($this->{$config} === null) {
            $this->{$config} = $newConfig;
        } else {
            $this->{$config} = array_merge($newConfig, $this->{$config});
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
        if (count($middleware) > 0) {
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
        $controller = $this->getOrDefault('controller', $config, null);
        if ($controller === null) {
            throw new \BadMethodCallException('Missing controller parameter for route ' . $name);
        }
        $route = $this->getOrDefault('route', $config, '/');
        $methods = explode('|', strtoupper($this->getOrDefault('methods', $config, 'GET')));
        $conditions = $this->getOrDefault('conditions', $config, []);
        $middleware = $this->getOrDefault('middleware', $config, []);
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
            $app->group($this->getOrDefault('route', $config, '/'), function () use (&$app, &$children, &$name) {
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
        foreach ($middleware as $name => $config) {

            $params = $this->getOrDefault('params', $config, []);
            if (is_array($config)) {
                if (array_key_exists('class', $config)) {
                    $class = $config['class'];
                    $readyMw[] = $this->getCallableFromString($class, $params);
                    continue;
                } else {
                    throw new \InvalidArgumentException('Middleware configuration lacking callable');
                }
            } else {
                $readyMw[] = $this->getCallableFromString($config);
            }
        }
        return $readyMw;
    }

    protected function getCallableFromString($class, $params)
    {
        $delimiter = strpos($class, '::') !== false ? '::' : ':';
        $cparams = explode($delimiter, $class);
        if (count($cparams) < 2) {
            throw new \InvalidArgumentException(
                'Class ' . $class . ' does not have a valid action; :: or : are '
                . 'required to delimit the method'
            );
        }
        if (count($params) > 0) {
            return call_user_func_array([$cparams[0], $cparams[1]], $params);
        } else {
            if ($delimiter === ':') {
                return [$cparams[0], $cparams[1]];
            } else {
                return $class;
            }
        }
    }

    /**
     * Validation for routes
     *
     * @return bool
     */
    public static function routeConfigValid(array $config)
    {
        $valid = true;
        if (!is_array($config)) {
            $valid = false;
        }

        foreach ($config as $name => $params) {
            if (!is_array($params)) {
                $valid = false;
                break;
            }
            $valid = $valid && self::routeBaseConfigValid($params);
        }

        return $valid;
    }

    /**
     * Checks if the group configuration is valid
     *
     * @return bool
     */
    public static function groupConfigValid(array $config)
    {
        $valid = true;
        if (!is_array($config)) {
            $valid = false;
        }

        foreach ($config as $name => $params) {
            $valid = $valid && self::validateGroupChild($params);
        }
        return $valid;
    }

    /**
     * Validates that the $nicknames array is an associative array with a string value (not multidimensional)
     *
     * @return bool
     */
    public static function nicknamesValid(array $nicknames)
    {
        $valid = true;
        if (!is_array($nicknames)) {
            $valid = false;
        }

        foreach ($nicknames as $nickname => $name) {
            if (is_array($name) && !is_callable($name)) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    /**
     * Validate if the passed params contain the minimum required keys
     * 
     * @param array $params
     * @return boolean
     */
    protected static function routeBaseConfigValid(array $params)
    {
        $valid = true;
        if (!array_key_exists('controller', $params)) {
            $valid = false;
        } else {
            if (is_array($params['controller'])) {
                $valid = false;
            }
        }
        if (!array_key_exists('route', $params)) {
            $valid = false;
        } else {
            if (is_array($params['route'])) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Recursive funtion to validate all the nodes from the group config
     *
     * @return bool
     */
    protected static function validateGroupChild($params)
    {
        $valid = true;

        if (is_array($params)) {
            if (!array_key_exists('route', $params)) {
                $valid = false;
            } else {
                if (array_key_exists('group', $params)) {
                    foreach ($params['group'] as $groupName => $groupParams) {
                        $valid = $valid && self::validateGroupChild($groupParams);
                    }
                } else {
                    $valid = $valid && self::routeBaseConfigValid($params);
                }
            }
        } else {
            $valid = false;
        }

        return $valid;
    }
}
