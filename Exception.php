<?php
namespace ice;

use ErrorException;

/**
 * Class Exception
 *
 * Exception of ice application
 *
 * @package ice
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Exception extends ErrorException
{
    /**
     * Error context data
     * @var array
     */
    private $errcontext = null;

    /**
     * Constructor exception of ice application
     *
     * Simple constructor for fast throws Exception
     *
     * @param string $errstr message of exception
     * @param array $errcontext context data of exception
     * @param Exception $previous previous exception if exists
     * @param string $errfile filename where throw Exception
     * @param int $errline number of line where throws Exception
     * @param int $errno code of error exception
     */
    public function __construct($errstr, $errcontext = [], $previous = null, $errfile = null, $errline = null, $errno = 0)
    {
        $this->errcontext = $errcontext;

//        $config = Config::get(__CLASS__);
//
//        $message = $config->get($errstr . '/' . Request::locale(), false);

//        if (!$message) {
        $message = $errstr;
//        }

        $debug = debug_backtrace();

        if (!$errfile) {
            /** @var Exception $exception */

            $exception = reset($debug)['object'];
            $errfile = $exception->getFile();
            $errline = $exception->getLine();
        }

        parent::__construct($message, $errno, 1, $errfile, $errline, $previous);
    }

    /**
     * Return error context data
     *
     * Data in moment throws exception
     *
     * @return array
     */
    public function getErrContext()
    {
        return $this->errcontext;
    }
}