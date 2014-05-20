<?php
namespace ice\helper;

/**
 * Helper Serializer
 *
 * @package ice\helper
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Serializer
{
    public static function serialize($data)
    {
        return Json::encode($data);
    }

    public static function unserialize($data)
    {
        return Json::decode($data);
    }
} 