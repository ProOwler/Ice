<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 25.04.14
 * Time: 18:10
 */

namespace ice\core;

use ice\data\provider\Router;

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