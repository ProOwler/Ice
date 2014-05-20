<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\Ice;
use ice\view\render\Php;

/**
 * Class Main. Hello world action.
 *
 * First step of creating module
 *
 * @see \ice\core\Action
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Main extends Action implements View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        return [
            'welcome' => 'Hello world',
            'enjoy' => 'Ice is Great!!!',
            'project' => Ice::getProject()
        ];
    }
}