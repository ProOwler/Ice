<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\Action_Context;

/**
 * Create action class

 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Create extends Action implements Cli
{

    /**
     * Run action
     *
     * @example
     * php app.php Ice:Create name=Blog:Post_Info // create action blog\action\Post_Info
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        var_dump($input);
    }
}