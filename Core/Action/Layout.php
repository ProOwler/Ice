<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;

/**
 * Layout action
 *
 * Common main action
 *
 * @package ice\core\action
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Layout extends Action implements View
{
    protected $layout = '';

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        if (isset($input['template'])) {
            $context->setTemplate($input['template']);
            unset($input['template']);
        }

        if (isset($input['viewRender'])) {
            $context->setViewRenderClass($input['viewRender']);
            unset($input['viewRender']);
        }

        foreach ($input['actions'] as $var => $action) {
            $context->addAction($action, $input, $var);
        }

        return [];
    }
}