<?php
namespace ice\core;

use ice\Exception;
use ice\helper\Dir;
use ice\Ice;

/**
 * Ice application logger
 *
 * @package ice\core
 * @author dp
 */
class Logger
{
    /** @var array Codes of error types */
    private static $errorTypes = [
        0 => 'Error',
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
    ];

    /**
     * Initialization logger
     */
    public static function init($isDebug)
    {
        error_reporting(E_ALL | E_STRICT);

        ini_set('display_errors', $isDebug);

        set_error_handler('ice\core\Logger::errorHandler');
        register_shutdown_function('ice\core\Logger::shutdownHandler');

        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.profiler_enable', 1);
        ini_set('xdebug.profiler_output_dir', Ice::getRootPath() . 'xdebug');

        require_once(Ice::getEnginePath() . 'Vendor/FirePHPCore/FirePHP.class.php');
        require_once(Ice::getEnginePath() . 'Vendor/FirePHPCore/fb.php');
        ob_start();
    }

    /**
     * Method of shutdown handler
     */
    public static function shutdownHandler()
    {
        if ($error = error_get_last()) {
            if (!headers_sent()) {
                header('HTTP/1.0 500 Internal Server Error');
            }

//            Ice::get(Ice::getProject())->display();

            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line'], debug_backtrace());
            die('Terminated. Bye-bye...');
        }
    }

    /**
     * Method of error handler
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        self::output(self::getMessage(new Exception($errstr, $errcontext, null, $errfile, $errline, $errno)));
    }

    /**
     * Output log to default output stream
     *
     * @param $message
     */
    public static function output($message)
    {
        echo $message;
    }

    /**
     * Handle error exception
     *
     * @param \Exception $exception
     */
    public static function getMessage(\Exception $exception)
    {
        $e = $exception->getPrevious();

        if ($e) {
            self::getMessage($e);
        }

        $delimetr = '[' . date('Y-m-d H:i:s') . '] host: ' . Request::host() . ' | ' . str_repeat('-', 100);

        $errcontext = $exception instanceof Exception
            ? $exception->getErrContext()
            : [];

        $log = [];
        $log['message'] = self::$errorTypes[$exception->getCode()] . ': ' . $exception->getMessage();
        $log['errPoint'] = '(' . $exception->getFile() . ':' . $exception->getLine() . ')';
        if (!empty($errcontext)) {
            $log['errcontext'] = print_r($errcontext, true);
        }
        $log['stackTrace'] = $exception->getTraceAsString();

        Logger::log($delimetr . "\n" . implode("\n", $log) . "\n\n");

        $message = '<meta charset="utf-8"/>';
        $message .= '<div class="alert alert-danger" style="font-size: 10px;font-family: Tahoma, Geneva, sans-serif;">';
        $message .= '<strong style="color: red;">' . $log['message'] . '</strong> <em style="color: blue;">' . $log['errPoint'] . '</em><br/>';
        if (!empty($errcontext)) {
            $message .= '<a style="color:grey; text-decoration: none; border-bottom:1px dashed;" href="#" onclick="$(\'.errcontext\').show();">errcontext</a><br/>';
            $message .= '<pre class="errcontext" style="color: green;/* display: none;*/">' . print_r(
                    $errcontext,
                    true
                ) . '</pre>';
        }
        $message .= nl2br($log['stackTrace'], true);
        $message .= '</div>';

        if (function_exists('fb')) {
            fb($delimetr);
            fb($log['message'] . $log['errPoint'], 'ERROR');
            if (!empty($errcontext)) {
                fb($errcontext, 'INFO');
            }
            fb(explode("\n", $log['stackTrace']), 'WARN');
        }

        return $message;
    }

    /**
     * Save log to file
     *
     * @param $message
     */
    public static function log($message)
    {
        $logDir = Ice::getRootPath() . 'log/' . Ice::getProject() . '/';
        $logFile = Dir::get($logDir) . 'error_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}