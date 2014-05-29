<?php
namespace ice\core;

use ice\Exception;
use ice\helper\Dir;
use ice\helper\File;
use ice\Ice;
use Iterator;

/**
 * Config class
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Config implements Iterator
{
    /** @var array Config params */
    private $_config = null;

    /** @var null Config Key */
    private $_configName = null;

    private $position = 0;

    /**
     * Constructor of config object
     *
     * @param array $_config
     * @param $configName
     */
    public function __construct(array $_config, $configName)
    {
        $this->_config = $_config;
        $this->_configName = $configName;
    }

    /**
     * Create file config
     *
     * @param $className
     * @param array $configData
     * @param null $postfix
     * @param bool $force
     * @throws Exception
     * @return Config
     */
    public static function create($className, array $configData, $postfix = null, $force = false)
    {
        $configName = $postfix
            ? $className . '_' . $postfix
            : $className;

        $filePath = '';

        foreach (explode('\\', $configName) as $filePathPart) {
            $filePathPart[0] = strtoupper($filePathPart[0]);
            $filePath .= $filePathPart . '/';
        }

        $fileName = Ice::getProjectPath() . 'Config/' . str_replace('_', '/', rtrim($filePath, '/')) . '.php';

        if (file_exists($fileName) && !$force) {
            throw new Exception('Config file "' . $fileName . '" already exists');
        }

        File::createData($fileName, $configData);

        return Config::getInstance($className, [], $postfix, true, false);
    }

    /**
     * Get config object by type or key
     *
     * @param $className
     * @param array $selfConfig
     * @param null $postfix
     * @param bool $isRequired
     * @param bool $isUseCache
     * @throws Exception
     * @return Config
     */
    public static function getInstance(
        $className,
        array $selfConfig = [],
        $postfix = null,
        $isRequired = false,
        $isUseCache = true
    )
    {
        if ($postfix) {
            $className .= '_' . $postfix;
        }

        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $_config = $isUseCache ? $dataProvider->get($className) : null;

        if ($_config) {
            return $_config;
        }

        $config = [];

        foreach (Ice::getModules() as $modulePath) {
            $filePath = '';

            foreach (explode('\\', $className) as $filePathPart) {
                $filePathPart[0] = strtoupper($filePathPart[0]);
                $filePath .= $filePathPart . '/';
            }

            $fileName = $modulePath . 'Config/' . str_replace('_', '/', rtrim($filePath, '/')) . '.php';

            if (file_exists($fileName)) {
                $configFromFile = include $fileName;

                if (!is_array($configFromFile)) {
                    throw new Exception('Не валидный файл конфиг: ' . $fileName);
                }

                $config += $configFromFile; // http://www.php.net/array_merge => оператор +
            }
        }

        $iceConfig = Ice::getConfig()->gets('configs/' . $className, false);

        if (!empty($iceConfig)) {
            $config += $iceConfig;
        }

        $config += $selfConfig;

        if (empty($config)) {
            return null;
        }

        $_config = new Config($config, $className);

        $dataProvider->set($className, $_config);

        return $_config;
    }

    /**
     * Get config param value
     *
     * @param $key
     * @param bool $isRequired
     * @throws Exception
     * @return string
     */
    public function get($key = null, $isRequired = true)
    {
        $param = null;

        try {
            $param = $this->xpath($this->_config, $key, $isRequired);
        } catch (\Exception $e) {
            throw new Exception('Could nof found config param -> ' . $this->getConfigName() . ':' . $key, [], $e);
        }

        if (is_array($param)) {
            $param = reset($param);
        }

        return $param;
    }

    /**
     * Return param by string
     *
     * @param $config
     * @param $key
     * @param $isRequired
     * @return array
     * @throws Exception
     */
    private function xpath($config, $key, $isRequired)
    {
        if (!$key) {
            return $config;
        }

        $pos = strpos($key, '/');

        if ($pos === false) {
            $param = isset($config[$key]) ? $config[$key] : null;

            if ($param === null && $isRequired) {
                throw new Exception('Could nof found config required param -> ' . $this->getConfigName() . ':' . $key);
            }

            return $param;
        }

        $_key = substr($key, 0, $pos);
        $param = isset($config[$_key]) ? $config[$_key] : null;

        if ($param === null) {
            if ($isRequired) {
                throw new Exception('Could nof found config required param -> ' . $this->getConfigName() . ':' . $key);
            } else {
                return $param;
            }
        }

        return $this->xpath($param, substr($key, $pos + 1), $isRequired);
    }

    /**
     * Return config name
     *
     * @return string
     */
    public function getConfigName()
    {
        return $this->_configName;
    }

    /**
     * Get more then one params of config
     *
     * @param $key
     * @param bool $isRequired
     * @throws Exception
     * @return array
     */
    public function gets($key = null, $isRequired = true)
    {
        $params = null;

        try {
            $params = (array) $this->xpath($this->_config, $key, $isRequired);
        } catch (\Exception $e) {
            throw new Exception('Could nof found config params -> ' . $this->getConfigName() . ':' . $key, [], $e);
        }

        return $params;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return new Config((array)current($this->_config), $this->_configName . '_' . $this->position);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->_config);
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return !empty(current($this->_config));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (!empty($this->_config)) {
            reset($this->_config);
        }

        $this->position = 0;
    }
}