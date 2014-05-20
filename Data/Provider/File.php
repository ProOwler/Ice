<?php

namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;
use ice\helper\Dir;

/**
 * Data provider for file storage
 *
 * @package ice\data\provider
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class File extends Data_Provider
{
    public static $connections = [];

    public function get($key = null)
    {
        $fileName = $this->getFileName($key);

        if (!file_exists($fileName)) {
            return null;
        }

        return include $fileName;
    }

    private function getFileName($key)
    {
        return $this->getConnection() . $this->getKey($key) . '.php';
    }

    public function set($key, $value, $ttl = 3600)
    {
        $fileName = $this->getFileName($key);
        return \ice\helper\File::createData($fileName, $value);
    }

    public function delete($key)
    {
        $fileName = $this->getFileName($key);

        if (file_exists($fileName)) {
            unlink($fileName);
        }
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
        $connection = $this->getOption('path');
        return (bool)Dir::get($connection);
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function close(&$connection)
    {
        return true;
    }
}