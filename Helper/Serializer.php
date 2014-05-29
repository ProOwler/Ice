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
        return serialize($data);
    }

    public static function unserialize($data)
    {
        return unserialize($data);
    }
} 