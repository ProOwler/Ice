<?php
namespace ice\data\provider;

use ice\core\Data_Provider;


/**
 * Null data provider
 *
 * @package ice\data\provider
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Null extends Data_Provider {

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
        return true;
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function close(&$connection)
    {
        return true;
    }

    public function get($key = null)
    {
        return null;
    }

    public function set($key, $value, $ttl = 3600)
    {
       return true;
    }

    public function delete($key)
    {
        return true;
    }

    public function inc($key, $step = 1)
    {
        return true;
    }

    public function dec($key, $step = 1)
    {
        return true;
    }

    public function flushAll()
    {
        return true;
    }
}