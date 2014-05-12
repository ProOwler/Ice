<?php
namespace ice\core\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\view\render\Php;

class Front_Cli extends Action implements Cli, View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
    protected $dataProviderKeys = \ice\data\provider\Cli::DEFAULT_KEY;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        ini_set('memory_limit', '1024M');

        $action = $input['action'];
        unset($input['action']);

        $context->addAction($action, $input);
    }
}