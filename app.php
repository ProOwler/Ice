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
 * @author dp
 */

require_once './Ice.php';

ice\Ice::get(basename(__DIR__))->run()->flush();
