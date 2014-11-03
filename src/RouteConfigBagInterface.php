<?php

namespace ecoreng\Route;

interface RouteConfigBagInterface
{
    public function getNicknames();

    public function getGroupConfig();

    public function getRouteConfig();

    public function setGroupConfig(array $config);

    public function setRouteConfig(array $config);

    public function setNicknames(array $nicknames);

    public static function getOrDefault($key, array $haystack, $default);
}
