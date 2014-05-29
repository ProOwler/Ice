<?php
namespace ice\action;

use ice\core\action\Layout;
use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

/**
 * Default layout action
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Layout
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Layout_Main extends Layout
{
    protected $staticActions = 'Ice:Html_Head_Resources';
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
}