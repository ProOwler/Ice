<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\Exception;
use ice\view\render\Php;

/**
 * Class Http_Status_404
 *
 * @see \ice\core\Action
 * @see \ice\core\Action_Context;
 * @see \ice\core\action\View;
 * @package ice\action
 * @author dp
 * @since -0
 */
class Http_Status_404 extends Action implements View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @throws Exception
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        header('HTTP/1.0 404 Not Found');
    }
}