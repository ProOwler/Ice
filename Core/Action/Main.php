<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

/**
 * Main action
 *
 * Default action
 *
 * @package ice\core\action
 * @author dp
 */
class Main extends Action
{
    /**
     * Initialization action context
     *
     * @return Action_Context
     */
    protected function init()
    {
        $actionContext = parent::init();
        $actionContext->setViewRenderClass(Php::VIEW_RENDER_PHP_CLASS);
        return $actionContext;
    }

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        return [
            'welcome' => 'Hello world',
            'test' => 'test'
        ];
    }
}