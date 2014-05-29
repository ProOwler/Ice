<?php
namespace ice\helper;

use ice\core\Config;
use ice\Exception;
use ice\Ice;

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
            throw new Exception('Prefix "' . $prefix . '" for class "' . $class . '" not found');
        }

        return Config::getInstance($class)->get($prefix) . $modelName;
    }

    public static function getNamespaceByClassShortName($class, $shortName)
    {
        $class = self::getClassByClassShortName($class, $shortName);
        return substr(strstr($class, Object::getName($class), true), 0, -1);
    }

    public static function getPrefixByClassShortName($class, $shortName)
    {
        $namespace = self::getNamespaceByClassShortName($class, $shortName) . '\\';
        $prefixes = array_flip(Config::getInstance($class)->gets());
        return isset($prefixes[$namespace]) ? $prefixes[$namespace] : Ice::ENGINE ;
    }
} 