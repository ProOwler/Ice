<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

/**
 * Main action
 *
 * Default action
 *
 * @package ice\core\action
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Main extends Action
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        return [
            'welcome' => 'Hello world',
            'test' => 'test'
        ];
    }
}