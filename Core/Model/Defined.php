<?php
namespace ice\core\model;

use ice\core\Config;
use ice\core\Model;

class Defined extends Model
{
    public static function getDefinedConfig()
    {
        return Config::getInstance(get_called_class(), [], 'Defined', true);
    }
} 