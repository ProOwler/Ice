<?php
namespace ice\helper;

use ice\core\Config;
use ice\Exception;

class Object
{
    public static function getName($objectClass)
    {
        if (!strpos(ltrim($objectClass, '\\'), '\\')) {
            return $objectClass;
        }

        return substr($objectClass, strrpos($objectClass, '\\') + 1);
    }

    public static function getClassByClassShortName($class, $shortName)
    {
        if (strpos($shortName, ':') === false) {
            return $shortName;
        }

        list($prefix, $modelName) = explode(':', $shortName);

        $config = Config::getInstance($class);

        if (!$config) {
            throw new Exception('Prefix "' . $prefix .  '" for class "' . $class . '" not found');
        }

        return Config::getInstance($class)->get($prefix) . $modelName;
    }
} 