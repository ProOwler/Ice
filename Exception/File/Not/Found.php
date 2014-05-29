<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 24.05.14
 * Time: 16:45
 */

namespace ice\exception;

use ice\Exception;

class File_Not_Found extends Exception
{
    public function __construct($errstr, $errcontext = [], $previous = null, $errfile = null, $errline = null, $errno = 0)
    {
        parent::__construct('FileNotFoundException: ' . $errstr, $errcontext, $previous, $errfile, $errline, $errno);
    }

} 