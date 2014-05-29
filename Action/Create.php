<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\core\Loader;
use ice\helper\Object;
use ice\view\render\Php;

/**
 * Create action class

 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Create extends Action implements Cli, View
{
    protected $inputValidators = [
        'name' => 'Ice:Not_Empty'
    ];

    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @example
     * php app.php Ice:Create name=Blog:Post_Info // create action blog\action\Post_Info
     *
     * @param array $input
     * @param Action_Context $actionContext
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
        $actionContext->setOutput('File:output/' . Loader::getFilePath($actionClass, '.php', '', false, true, true));

        View_Create::call(['name' => $input['name']])->display();

        return [
            'namespace' => Object::getNamespaceByClassShortName(Action::getClass(), $action),
            'actionName' => Object::getName($actionClass),
            'interfaces' => isset($input['interfaces']) ? explode(',', $input['interfaces']) : []
        ];
    }
}