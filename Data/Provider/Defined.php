<?php
namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\core\Model;
use ice\Exception;

class Defined extends Data_Provider
{
    public static $connections = [];

    public function get($key = null)
    {
        throw new Exception('Implement get() method.');
    }

    public function set($key, $value, $ttl = 3600)
    {
        throw new Exception('Implement set() method.');
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
        /** @var Model $modelName */
        $modelName = $this->getScheme();
        $connection = $modelName::getDefinedConfig()->gets();
        return !empty($connection);
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