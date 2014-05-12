<?php
namespace ice\data\provider;

use ArrayObject;
use ice\core\Data_Provider;
use ice\Exception;

class Registry extends Data_Provider
{
    public static $connections = [];

    public static function getDefaultKey()
    {
        return \ice\core\Registry::DEFAULT_DATA_PROVIDER_KEY;
    }

    public function get($key = null)
    {
        $keyPrefix = $this->getKeyPrefix();

        if (!isset($this->getConnection()->$keyPrefix)) {
            return null;
        }

        $data = $this->getConnection()->$keyPrefix;

        if ($key === null) {
            return $data;
        }

        return isset($data[$key]) ? $data[$key] : null;
    }

    /** @return ArrayObject */
    public function getConnection()
    {
        return parent::getConnection();
    }

    public function set($key, $value, $ttl = 3600)
    {
        if (is_array($key) && $value === null) {
            foreach ($key as $k => $value) {
                $this->set($key, $value, $ttl);
            }

            return;
        }

        $keyPrefix = $this->getKeyPrefix();

        if (!isset($this->getConnection()->$keyPrefix)) {
            $this->getConnection()->$keyPrefix = [];
        }

        $data = & $this->getConnection()->$keyPrefix;
        $data[$key] = $value;
    }

    public function delete($key)
    {
        throw new Exception('Implement delete() method.');
    }

    public function inc($key, $step = 1)
    {
        throw new Exception('Implement inc() method.');
    }

    public function dec($key, $step = 1)
    {
        throw new Exception('Implement dec() method.');
    }

    public function flushAll()
    {
        throw new Exception('Implement flushAll() method.');
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function connect(&$connection)
    {
        $connection = new ArrayObject();
        return true;
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function close(&$connection)
    {
        $connection = null;
        return true;
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function switchScheme(&$connection)
    {
        return true;
    }
}