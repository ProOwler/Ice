<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 15.03.14
 * Time: 14:38
 */

namespace ice\action;

use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\view\render\Php;

class Main extends Action implements View
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

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        return ['welcome'=> 'Hello world!', 'enjoy' => 'Ice is Great!!!'];
    }
}