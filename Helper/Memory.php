<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 08.05.14
 * Time: 14:16
 */

namespace ice\helper;

class Memory
{
    public static function getVarSize($var)
    {
        $start_memory = memory_get_usage();
        $tmp = unserialize(serialize($var));
        return memory_get_usage() - $start_memory;
    }
} 