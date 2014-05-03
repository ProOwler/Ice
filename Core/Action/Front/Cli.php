<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

class Front_Cli extends Action implements Cli
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
        $actionContext->addDataProviderKeys('Cli:prompt/');

        ini_set('memory_limit', '1024M');

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
        $action = $input['action'];
        unset($input['action']);

        $context->addAction($action, $input);
    }
}