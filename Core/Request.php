<?php
namespace ice\core;

use Locale;

class Request
{
    public static function getParam($paramName)
    {
        $params = self::getParams();
        return isset($params[$paramName]) ? $params[$paramName] : null;
    }

    public static function getParams()
    {
        return \ice\data\provider\Request::getInstance()->get();
    }

    public static function locale()
    {
        return isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            ? Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            : 'en_US';
    }

    public static function uri($withoutQueryString = false)
    {
        return isset($_SERVER['REQUEST_URI'])
            ? ($withoutQueryString ? strtok($_SERVER["REQUEST_URI"],'?') : $_SERVER['REQUEST_URI'])
            : 'php://input';
    }

    public static function queryString()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    public static function host()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
        }

        return $_SERVER['HTTP_HOST'];
    }

    public static function ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var(
                $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP
            )
        ) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
//        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
//            return $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '0.0.0.0';
    }

    public static function agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : (isset($_SERVER['SHELL'])
                ? $_SERVER['SHELL']
                : 'unknown');
    }

    public static function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
}