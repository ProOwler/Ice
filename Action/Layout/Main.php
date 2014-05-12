<?php
namespace ice\action;

use ice\core\action\Layout;
use ice\core\Action;
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

    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
}