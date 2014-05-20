<?php
namespace ice\action;

use ice\core\action\Legacy;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\view\render\Smarty;

/**
 * Legacy layout action
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Legacy
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Layout_Legacy extends Legacy implements View
{
    protected $viewRenderClass = Smarty::VIEW_RENDER_SMARTY_CLASS;

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