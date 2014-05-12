<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 15.03.14
 * Time: 18:59
 */

namespace ice\action;

use ice\core\action\View;
use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

class Title extends Action implements View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        return $input;
    }
}