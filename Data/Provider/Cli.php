<?php
namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;

class Cli extends Data_Provider
{
    const DEFAULT_KEY = 'Cli:prompt/default';

    public static $connections = [];

    public static function getDefaultKey()
    {
        return self::DEFAULT_KEY;
    }

    public function get($key = null)
    {
        $connection = $this->getConnection();

        if (!$connection) {
            return null;
        }

        return $key ? $connection[$key] : $connection;
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
    protected function switchScheme(&$connection)
    {
        return true;
    }

    /**
     * @param $connection
     * @throws Exception
     * @return boolean
     */
    protected function connect(&$connection)
    {
        if (!isset($_SERVER ['argv']) || !isset($_SERVER ['argc'])) {
            throw new Exception('This script is for console use only.');
        }

        if (empty ($_SERVER ['argv']) || count($_SERVER ['argv']) < 2) {
            throw new Exception('Invalid command line. Usage: cli.php Action_Call param=value');
        }

        $connection = [];

        $connection['action'] = next($_SERVER ['argv']);

        while ($arg = next($_SERVER ['argv'])) {
            list($param, $value) = explode('=', $arg);
            if (!$value) {
                throw new Exception('Invalid command line. Usage: cli.php Action_Call param=value');
            }
            $connection[$param] = $value;
        }

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
}