<?php

namespace ice\action;

use ice\core\Action;
use ice\core\action\Layout;
use ice\core\Action_Context;
use ice\core\View;
use ice\view\render\Php;

/**
 * Action with blank output
 *
 * @package ice\action
 * @author dp
 */
class Layout_Blank extends Layout
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
}