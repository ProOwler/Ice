<?php
namespace ice\core;

use ice\Exception;
use ice\helper\Object;
use ice\Ice;

abstract class Data_Provider
{
    const DEFAULT_SCHEME = 'default';

    public static $connections = [];

    /** @var Data_Provider[] */
    private static $_dataProviders = [];

    private $_index = null; // default || production
    private $_scheme = null;
    private $_options = null;

    private function __construct($name, $index, $scheme, array $options = [])
    {
        $this->_name = $name;
        $this->_index = $index;
        $this->_scheme = $scheme;
        $this->_options = $options;
    }

    public static function getDefaultKey()
    {
        throw new Exception('Default data provider key is not defined');
    }

    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
    }

    /**
     * Get instance connection of data provider
     *
     * @throws Exception
     * @return Object
     */
    public function getConnection()
    {
        /** @var Data_Provider $dataProviderClass */
        $dataProviderClass = get_class($this);

        $dataProviderIndex = $this->getIndex();
        $dataProviderScheme = $this->getScheme();

        if (isset($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme])) {
            return $dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme];
        }

        if (!isset($dataProviderClass::$connections[$dataProviderIndex])) {
            $dataProviderClass::$connections[$dataProviderIndex] = [];
        }

        if (!empty($dataProviderClass::$connections[$dataProviderIndex])) {
            $oldConnection = each($dataProviderClass::$connections[$dataProviderIndex]);

            $dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme]
                = $dataProviderClass::$connections[$dataProviderIndex][$oldConnection['key']];

            if (!$this->switchScheme($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme])) {
                unset($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme]);
                throw new Exception('Не удалось переключиться к схеме дата провайдера "' . $this->getDataProviderKey() . '"');
            }

            if ($oldConnection['key'] != $dataProviderScheme) {
                if (!$this->connect($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme])) {
                    throw new Exception('Соединение с дата провайдером "' . $this->getDataProviderKey() . '" не установлено');
                }
            }

            unset($dataProviderClass::$connections[$dataProviderIndex][$oldConnection['key']]);
            return $dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme];
        }

        $dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme] = null;

        if (!$this->connect($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme])) {
            throw new Exception('Соединение с дата провайдером "' . $this->getDataProviderKey() . '" не установлено');
        }

        if (!$this->switchScheme($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme])) {
            unset($dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme]);
            throw new Exception('Не удалось переключиться к схеме дата провайдера "' . $this->getDataProviderKey() . '"');
        }

        return $dataProviderClass::$connections[$dataProviderIndex][$dataProviderScheme];
    }

    public function closeConnection()
    {
        $dataProviderName = $this->getName();
        $dataProviderIndex = $this->getIndex();
        $dataProviderScheme = $this->getScheme();

        if (!self::$_connectionPool[$dataProviderName][$dataProviderIndex][$dataProviderScheme]) {
            return;
        }

        if (!$this->close(self::$_connectionPool[$dataProviderName][$dataProviderIndex][$dataProviderScheme])) {
            throw new Exception('Не удалось закрыть соединенеие с дата провайдером "' . $this->getDataProviderKey() . '"');
        }

        unset(self::$_connectionPool[$dataProviderName][$dataProviderIndex][$dataProviderScheme]);
    }

    /**
     * @param $dataProviderKey // example: 'Redis:localhost/model'
     * @throws \ice\Exception
     * @return Data_Provider
     */
    public static function getInstance($dataProviderKey = null)
    {
        if (empty($dataProviderKey)) {
            /** @var Data_Provider $dataProviderClass */
            $dataProviderClass = get_called_class();
            $dataProviderKey = $dataProviderClass::getDefaultKey();
        }

        $index = strstr($dataProviderKey, '/', true);
        $dataProviderScheme = substr(strstr($dataProviderKey, '/'), 1);

        if (empty($dataProviderScheme)) {
            $dataProviderScheme = self::DEFAULT_SCHEME;
        }

        list($dataProviderName, $dataProviderIndex) = explode(':', $index);

        if (isset(self::$_dataProviders[$dataProviderName][$dataProviderIndex][$dataProviderScheme])) {
            return self::$_dataProviders[$dataProviderName][$dataProviderIndex][$dataProviderScheme];
        }

        $dataProviderClass = 'ice\data\provider\\' . $dataProviderName;

        if (empty(self::$_dataProviders[$dataProviderName])) {
            $filePath = '';

            foreach (explode('\\', $dataProviderClass) as $filePathPart) {
                $filePathPart[0] = strtoupper($filePathPart[0]);
                $filePath .= '/' . $filePathPart;
            }

            foreach (Ice::getModules() as $modulePath) {
                $fileName = dirname($modulePath) . str_replace('_', '/', $filePath) . '.php';
                if (file_exists($fileName)) {
                    require_once $fileName;
                    break;
                }
            }
        }

        self::$_dataProviders[$dataProviderName][$dataProviderIndex][$dataProviderScheme] =
            new $dataProviderClass(
                $dataProviderName,
                $dataProviderIndex,
                $dataProviderScheme,
                Ice::getEnvironment()->gets('dataProviders/' . $index)
            );

        return self::$_dataProviders[$dataProviderName][$dataProviderIndex][$dataProviderScheme];
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->_index;
    }

    protected function getKeyPrefix()
    {
        return Object::getName(get_class($this)) . '/' . $this->getIndex() . '/' . urlencode($this->getScheme());
    }

    protected function getKey($key)
    {
        return str_replace(['\\'], '/', Ice::getProject() . '/' . $this->getKeyPrefix() . '/' . urlencode($key));
    }

    protected function getOption($key = null)
    {
        $option = null;

        if ($key) {
            $option = $this->_options[$key];
            return is_array($option) ? reset($option) : $option;
        }

        return $this->_options;
    }

    /**
     * @param $connection
     * @return boolean
     */
    abstract protected function switchScheme(&$connection);

    /**
     * @param $connection
     * @return boolean
     */
    abstract protected function connect(&$connection);

    /**
     * @param $connection
     * @return boolean
     */
    abstract protected function close(&$connection);

    abstract public function get($key = null);

    abstract public function set($key, $value, $ttl = 3600);

    abstract public function delete($key);

    abstract public function inc($key, $step = 1);

    abstract public function dec($key, $step = 1);

    abstract public function flushAll();
}