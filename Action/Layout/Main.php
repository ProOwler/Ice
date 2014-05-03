<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\Layout;
use ice\core\Action_Context;
use ice\view\render\Php;

/**
 * Default layout action
 *
 * @package ice\action
 * @author dp
 */
class Layout_Main extends Layout
{
    protected $staticActions = [
        '\ice\action\Html_Head_Resources'
    ];

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
}