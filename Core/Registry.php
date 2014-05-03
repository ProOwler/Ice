<?php
namespace ice\core;

/**
 * Helper Registry
 *
 * Store data in current session
 *
 * @package ice\helper
 */
class Registry
{
    const DEFAULT_DATA_PROVIDER_KEY = 'Registry:registry/';

    public static function get($key)
    {
        return \ice\data\provider\Registry::getInstance()->get($key);
    }

    public static function set($key, $value)
    {
        \ice\data\provider\Registry::getInstance()->set($key, $value);
    }
}