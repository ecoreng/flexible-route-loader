<?php

namespace ecoreng\Route;

use \ecoreng\Route\RouteConfigBagInterface as BagInterface;

class RouteConfigBag implements BagInterface
{

    use \ecoreng\Route\ConfigHandler;

    protected $nicknames = [];
    protected $groupConfig = [];
    protected $routeConfig = [];

    public function getNicknames()
    {
        return $this->nicknames;
    }

    public function getGroupConfig()
    {
        return $this->groupConfig;
    }

    public function getRouteConfig()
    {
        return $this->routeConfig;
    }

    public function setGroupConfig(array $config)
    {
        if (self::groupConfigValid($config)) {
            $this->mergeConfigs('groupConfig', $config);
        } else {
            throw new \InvalidArgumentException('config array is not a valid group config array');
        }
    }

    public function setRouteConfig(array $config)
    {
        if (self::routeConfigValid($config)) {
            $this->mergeConfigs('routeConfig', $config);
        } else {
            throw new \InvalidArgumentException('config array is not a valid route config array');
        }
    }

    public function setNicknames(array $nicknames)
    {
        if (self::nicknamesValid($nicknames)) {
            $this->mergeConfigs('nicknames', $nicknames);
        } else {
            throw new \InvalidArgumentException('nicknames array is not a valid nicknames array');
        }
    }
}
