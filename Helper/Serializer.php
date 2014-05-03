<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 24.04.14
 * Time: 17:07
 */

namespace ice\helper;


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