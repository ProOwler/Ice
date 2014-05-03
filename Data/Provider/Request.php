<?php
namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;
use Locale;

class Request extends Data_Provider
{
    public static $connections = [];

    public static function getInstance($dataProviderKey = null)
    {
        if (!$dataProviderKey) {
            $dataProviderKey = self::getDefaultKey();
        }

        return parent::getInstance($dataProviderKey);
    }


    public static function getDefaultKey()
    {
        return \ice\core\Request::DEFAULT_DATA_PROVIDER_KEY;
    }

    public function get($key = null)
    {
        $connection = $this->getConnection();

        if (!$connection) {
            return null;
        }

        if ($key === null) {
            return $connection;
        }

        return isset($connection[$key]) ? $connection[$key] : null;
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
        if (!isset($_SERVER)) {
            return false;
        }

        $connection = (array)$_REQUEST;
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