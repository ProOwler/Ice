<?php
namespace ice\core;

use ice\Exception;
use ice\Ice;

/**
 * Class Loader
 *
 * @package ice\core
 * @author dp
 */
class Loader
{
    /** @var array Registrered autoloaders */
    private static $_autoLoaders = [];

    /**
     * Load class
     *
     * @param $class
     * @throws \ice\Exception
     */
    public static function load($class)
    {
        if (class_exists($class)) {
            return;
        }

        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $fileName = $dataProvider->get($class);
        if ($fileName) {
            require_once $fileName;
            return;
        }

        $fileName = self::getFilePath($class, '.php');

//        if (function_exists('fb')) {
//            fb($fileName);
//        }

        if ($fileName) {
            $dataProvider->set($class, $fileName);
            require_once $fileName;
        }
    }

    /**
     * Return class path
     *
     * @param $class
     * @param $path
     * @param $ext
     * @param bool $isRequired
     * @param bool $isNotNull
     * @param bool $isOnlyFirst
     * @throws Exception
     * @return null|string
     */
    public static function getFilePath(
        $class,
        $ext,
        $path = '',
        $isRequired = true,
        $isNotNull = false,
        $isOnlyFirst = false
    )
    {
        $fileName = null;

        $stack = [];

        $extClass = explode(':', $class);
        if (count($extClass) == 2) {
            list($path, $class) = $extClass;
        }

        foreach (Ice::getModules() as $modulePath) {
            $isNotLegacy = strpos(ltrim($class, '\\'), '\\');

            if ($isNotLegacy) {
                $modulePath = substr($modulePath, 0, strrpos($modulePath, '/', -2)) . '/';
            }

            $typePathes = [];
            $typePathes[] = $path ? $path . '/' : $path;

            $filePath = '';
            foreach (explode('\\', $class) as $filePathPart) {
                $filePathPart[0] = strtoupper($filePathPart[0]);
                $filePath .= $filePathPart . '/';
            }

            $filePath = str_replace('_', '/', rtrim($filePath, '/'));

            if (!$isNotLegacy && !$path) {
                array_push($typePathes, 'Model/', 'Class/');
            }

            foreach ($typePathes as $typePath) {
                $fileName = $modulePath . $typePath . $filePath . $ext;

                $stack[] = $fileName;

//                if (function_exists('fb')) {
//                    fb($fileName . ' ' . (int)file_exists($fileName));
//                }

                if (file_exists($fileName)) {
                    return $fileName;
                }
            }

            if ($isOnlyFirst || $isNotLegacy) {
                break;
            }
        }

        if ($isRequired) {
            throw new Exception('File for "' . $class . '" not found', $stack);
        }

        return $isNotNull ? reset($stack) : null;
    }

    /**
     * @desc Подключение автозагрузки классов
     */
    public static function register($autoLoader)
    {
        foreach (self::$_autoLoaders as $loader) {
            spl_autoload_unregister($loader);
        }

        $autoLoaders = self::$_autoLoaders;
        array_unshift($autoLoaders, $autoLoader);
        self::$_autoLoaders = $autoLoaders;

        foreach (self::$_autoLoaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    /**
     * @desc Отключение автозагрузки классов
     */
    public static function unregister($autholoader)
    {
        foreach (self::$_autoLoaders as $key => $loader) {
            if ($loader == $autholoader) {
                spl_autoload_unregister($autholoader);
                unset(self::$_autoLoaders[$key]);
                break;
            }
        }
    }
} 