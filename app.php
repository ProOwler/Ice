<?php
/**
 * @file
 * Directory index file
 *
 * Run and flush ice application
 *
 * Example usage:
 * @code
 *  require_once './Ice.php';
 *  ice\Ice::get(basename(__DIR__))->run()->flush();
 * @endcode
 *
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */

require_once '../Ice/Ice.php';

ice\Ice::get(basename(__DIR__))->run()->flush();
