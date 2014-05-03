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
 * @package ice\action
 * @author dp
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