<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 01.04.14
 * Time: 18:15
 */

namespace ice\helper;

class String
{
    public static function serialize($value)
    {
        return serialize($value);
    }

    public static function unserialize($serializedString)
    {
        return unserialize($serializedString);
    }
} 