<?php
namespace ice\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\core\action\View;
use ice\core\action\Cli;
use ice\core\Loader;
use ice\Exception;
use ice\helper\Object;
use ice\Ice;

/**
 * Class View_Create
 *
 * @see \ice\core\Action
 * @see \ice\core\Action_Context;
 * @see \ice\core\action\View;
 * @see \ice\core\action\Cli;
 * @package ice\action
 * @author dp
 * @since -0
 */
class View_Create extends Action implements View, Cli
{
    protected $inputDefaults = ['ext' => '.php'];

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
        $action = null;
        $template = null;
        $parts = explode('/', $input['name']);
        if (count($parts) == 2) {
            list($action, $template) = $parts;
        } else {
            $action = $input['name'];
        }
        $actionClass = Object::getClassByClassShortName(Action::getClass(), $action);
        $prefix = Object::getPrefixByClassShortName(Action::getClass(), $action);
        $actionName = Object::getName($actionClass);
        $template = $prefix . '/' . (empty($template) ? $actionName : $actionName . '/' . $template);
        $file = Loader::getFilePath($template, $input['ext'], 'View/Template', false, true, true);
        $actionContext->setOutput('File:output/' . $file);
    }
}