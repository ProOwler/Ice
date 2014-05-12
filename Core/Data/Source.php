<?php
namespace ice\core;

use ice\Exception;
use ice\helper\Object;
use ice\Ice;

abstract class Data_Source
{
    const CONFIG_CACHE_DATA_PROVIDER = 'cacheDataProvider';
    const DEFAULT_CACHE_DATA_PROVIDER = 'File:cache/data_source';

    private static $_dataSources = [];

    private $_sourceDataProvider = null;
    private $_cacheDataProvider = null;

    private $_dataSourceKey = null;

    private function __construct($dataSourceKey)
    {
        $this->_dataSourceKey = $dataSourceKey;
    }

    public static function getDefault()
    {
        return self::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));
    }

    /**
     * @param $dataSourceKey // example: 'Mysqli:production/scheme'
     * @return Data_Source
     */
    public static function getInstance($dataSourceKey)
    {
        if (isset(self::$_dataSources[$dataSourceKey])) {
            return self::$_dataSources[$dataSourceKey];
        }

        $dataSourceClass = 'ice\data\source\\' . strstr($dataSourceKey, ':', true);
        self::$_dataSources[$dataSourceKey] = new $dataSourceClass($dataSourceKey);

        return self::$_dataSources[$dataSourceKey];
    }

    /**
     * @param Query $query
     * @throws Exception
     * @return array
     */
    abstract public function select(Query $query);

    /**
     * @param Query $query
     * @return array
     */
    abstract public function insert(Query $query);

    /**
     * @param Query $query
     * @return array
     */
    abstract public function update(Query $query);

    /**
     * @param Query $query
     * @return array
     */
    abstract public function delete(Query $query);

    public function getName()
    {
        return Object::getName(get_class($this));
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->getSourceDataProvider()->getScheme();
    }

    /**
     * @return Data_Provider
     */
    private function getSourceDataProvider()
    {
        if ($this->_sourceDataProvider !== null) {
            return $this->_sourceDataProvider;
        }

        $this->_sourceDataProvider = Data_Provider::getInstance($this->getDataSourceKey());

        return $this->_sourceDataProvider;
    }

    /**
     * @return string
     */
    public function getDataSourceKey()
    {
        return $this->_dataSourceKey;
    }

    /**
     * Get connection instance
     *
     * @param string|null $scheme
     * @return Object
     */
    public function getConnection($scheme = null)
    {
        if ($scheme) {
            $this->getSourceDataProvider()->setScheme($scheme);
        }

        return $this->getSourceDataProvider()->getConnection();
    }

    /**
     * @return Data_Provider
     */
    public function getCacheDataProvider()
    {
        if ($this->_cacheDataProvider !== null) {
            return $this->_cacheDataProvider;
        }

        $dataProviderKey = $this->getConfig()->get(
            $this->getDataSourceKey() . '/' . self::CONFIG_CACHE_DATA_PROVIDER
        );

        $this->_cacheDataProvider = isset($dataProviderKey)
            ? Data_Provider::getInstance($dataProviderKey)
            : Data_Provider::getInstance(self::DEFAULT_CACHE_DATA_PROVIDER);

        return $this->_cacheDataProvider;
    }

    public static function getConfig()
    {
        return Config::getInstance(get_called_class());
    }
}