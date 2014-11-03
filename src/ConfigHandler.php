<?php

namespace ecoreng\Route;

trait ConfigHandler
{

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
