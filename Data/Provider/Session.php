<?php
namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;

class Session extends Data_Provider
{
    public static $connections = [];

    /**
     * @param $connection
     * @return boolean
     */
    protected function switchScheme(&$connection)
    {
        return true;
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function connect(&$connection)
    {
        if (!isset($_SESSION)) {
            session_start();
            $_SESSION['PHPSESSID'] = session_id();
            $connection = $_SESSION;
        }

        return isset($connection);
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function close(&$connection)
    {
        unset($_SESSION);
        return true;
    }

    public function get($key = null)
    {
        return $key ? $this->getConnection()[$key] : $this->getConnection();
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
}