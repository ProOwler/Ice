<?php
namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;

class Mysqli extends Data_Provider
{
    public static $connections = [];

    /**
     * Get instance connection of data provider
     * @return \Mysqli
     */
    public function getConnection()
    {
        return parent::getConnection();
    }

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
     * @param $connection \Mysqli
     * @throws Exception
     * @return bool
     */
    protected function connect(&$connection)
    {
        $connection = mysqli_init();

        $isConnected = $connection->real_connect(
            $this->getOption('host'),
            $this->getOption('username'),
            $this->getOption('password'),
            null,
            $this->getOption('port')
        );

        if (!$isConnected) {
            throw new Exception('#' . $connection->errno . ': ' . $connection->error);
        }

        $connection->set_charset($this->getOption('charset'));

        return $isConnected;
    }

    /**
     * @param $connection \Mysqli
     * @return boolean
     */
    protected function switchScheme(&$connection)
    {
        return $connection->select_db($this->getScheme());
    }

    /**
     * @param $connection \Mysqli
     * @return bool
     */
    protected function close(&$connection)
    {
        $connection->close();
        $connection = null;
        return true;
    }
}