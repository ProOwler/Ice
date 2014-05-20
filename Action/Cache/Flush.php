<?php
namespace ice\action;

use ice\core\action\Cli;
use ice\core\Action;
use ice\core\Action_Context;

/**
 * Flush all cache data providers
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Cache_Flush extends Action implements Cli
{
    protected $config = ['Apc', 'Redis'];

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
    }
}