<?php

namespace ice\core\action;

use ice\action\Layout_Legacy;
use ice\core\Action;
use ice\core\Action_Context;
use ice\core\Model;
use ice\data\provider\Router;
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

    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
    protected $dataProviderKeys = Router::DEFAULT_KEY;

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

        $layout = $input['layout'];

        if (isset($layout['template'])) {
            $params['template'] = $layout['template'];
        }

        if (isset($layout['viewRender'])) {
            $params['viewRender'] = $layout['viewRender'];
        }

        $action = $layout['action'];

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