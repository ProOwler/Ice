<?php
namespace ice\core;

use ice\data\provider\Router;

/**
 * Class Route
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Route
{

    public static function get()
    {
        return Router::getInstance()->get();
    }

    public static function getClass()
    {
        return __CLASS__;
    }

    public static function getConfig()
    {
        return Config::getInstance(__CLASS__);
    }
} 