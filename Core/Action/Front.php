<?php

namespace ice\core\action;

use ice\action\Layout_Legacy;
use ice\core\Action;
use ice\core\Action_Context;
use ice\core\Model;
use ice\data\provider\Router;
use ice\Ice;
use ice\view\render\Php;

/**
 * Entry point of app
 *
 * @package ice\core\action
 * @author dp
 */
class Front extends Action implements View
{
    const LEGACY_CONTENT = 'content';

    /**
     * Initialization action context
     *
     * @return Action_Context
     */
    protected function init()
    {
        $actionContext = parent::init();
        $actionContext->setViewRenderClass(Php::VIEW_RENDER_PHP_CLASS);
        $actionContext->addDataProviderKeys(Router::getDefaultKey());
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
        $params = $input['params'];
        $params['actions'] = $input['actions'];

        $layout =  $input['layout'];

        $action = $layout['action'];

        if (isset($layout['template'])) {
            $params['template'] = $layout['template'];
        }

        if (isset($layout['viewRender'])) {
            $params['viewRender'] = $layout['viewRender'];
        }

        /**
         * Legacy support
         *
         * IcEngine compatibility
         */
        if (strpos($action, '/')) {
            $params['action'] = $action;
            $action = Layout_Legacy::getClass();
        }

        $context->addAction($action, $params, 'front');
    }
}