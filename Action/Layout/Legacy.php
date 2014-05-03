<?php
namespace ice\action;

use ice\core\action\Legacy;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\view\render\Smarty;

/**
 * Legacy layout action
 *
 * @package ice\action
 * @author dp
 */
class Layout_Legacy extends Legacy implements View
{
    /**
     * Initialization action context
     *
     * @return Action_Context
     */
    protected function init()
    {
        $actionContext = parent::init();
        $actionContext->setViewRenderClass(Smarty::VIEW_RENDER_SMARTY_CLASS);
        return $actionContext;
    }

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @throws \ice\Exception
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        if (isset($input['layoutTemplate'])) {
            $context->setTemplate($input['layoutTemplate']);
            unset($input['layoutTemplate']);
        }

        $legacyAction = $input['action'];
        unset($input['action']);

        $action = reset($input['actions']);
        unset($input['actions']);

        $context->addAction($action, $input, 'content');

        $output = parent::run(['action' => $legacyAction], $context);

        $context->setTemplate($output['template']);
        unset($output['template']);

        return $output;
    }
}