<?php

namespace ice\action;

use ice\core\action\Layout;
use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

/**
 * Action with blank output
 *
 * @package ice\action
 * @author dp
 */
class Layout_Blank extends Layout
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
}