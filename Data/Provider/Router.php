<?php
namespace ice\data\provider;

use ice\core\Config;
use ice\core\Data_Provider;
use ice\core\Request;
use ice\core\Route;
use ice\Exception;
use ice\Ice;

class Router extends Data_Provider
{
    const DEFAULT_KEY = 'Router:route/default';
    public static $connections = [];

    public static function getDefaultKey()
    {
        return self::DEFAULT_KEY;
    }

    public function get($key = null)
    {
        $scheme = $this->getScheme();

        $url = $scheme == 'default' ? Request::uri() : $scheme;

        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . Route::getClass()));

        /** @var Route $_route */
        $_route = $dataProvider->get($url);
        if (!$_route) {
            foreach ($this->getConnection() as $routeConfig) {
                $route = $routeConfig->gets();
                $pattern = '#^' . $route['route'] . '$#';
                if (empty($route['patterns'])) {
                    $route['patterns'] = [];
                }
                foreach ($route['patterns'] as $var => $routeData) {
                    $replace = $routeData['pattern'];
                    $var = '{$' . $var . '}';
                    if (!empty($routeData['optional'])) {
                        $replace = '(?:' . $replace . ')?';
                    }
                    $pattern = str_replace($var, $replace, $pattern);
                }

//                fb($pattern . ' ' . (int)preg_match($pattern, $url));
//                fb($route);

                if (preg_match($pattern, $url)) {
                    if (empty($route['params'])) {
                        $route['params'] = [];
                    }
                    $route['params']['pattern'] = $pattern;
                    $_route = $route;
                    unset($route);
                    break;
                }
            }
        }

        if (!$_route) {
            return null;
        }

        $baseMatches = [];

        preg_match_all($_route['params']['pattern'], $url, $baseMatches);

        if (!empty($baseMatches[0][0]) && !empty($_route['patterns'])) {
            $keys = array_keys($_route['patterns']);

            foreach ($baseMatches as $i => $data) {
                if (!$i) {
                    continue;
                }
                if (!empty($data[0])) {
                    $_route['params'][$keys[$i - 1]] = $data[0];
                } else {
                    $part = $_route['patterns'][$keys[$i - 1]];
                    if (isset($part['default'])) {
                        $_route['params'][$keys[$i - 1]] = $part['default'];
                    }
                }
            }
        }

        unset($_route['patterns']);

        if (empty($_route['layout'])) {
            $_route['layout']['action'] = Ice::getConfig()->get('defaultLayoutAction');
        }

        $dataProvider->set('url', $_route);

        return $key ? $_route[$key] : $_route;
    }

    /**
     * Get instance connection of data provider
     * @throws Exception
     * @return Config[]
     */
    public function getConnection()
    {
        return parent::getConnection();
    }

    public function set($key, $value, $ttl = 3600)
    {
        throw new Exception('Not implemented!');
    }

    public function delete($key)
    {
        throw new Exception('Not implemented!');
    }

    public function inc($key, $step = 1)
    {
        throw new Exception('Not implemented!');
    }

    public function dec($key, $step = 1)
    {
        throw new Exception('Not implemented!');
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
        $connection = Route::getConfig();
        return (bool)$connection;
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