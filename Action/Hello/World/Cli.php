<?php

namespace ice\action;

use ice\core\action\Cli;
use ice\core\Action;
use ice\core\Action_Context;

/**
 * Hello world cli action
 *
 * Test run cli action
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Hello_World_Cli extends Action implements Cli
{
    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        echo 'Hello_World' . "\n";
    }
}