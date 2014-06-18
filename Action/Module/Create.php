<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\helper\Dir;
use ice\helper\File;
use ice\Ice;
use ice\view\render\Php;

/**
 * Create module
 *
 * Action create module dir, generate config and coping index file app.php
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Module_Create extends Action implements Cli, View
{

    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    protected $inputValidators = [
        'name' => 'Ice:Not_Null'
    ];

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        $moduleName = ucfirst($input['name']);

        if (file_exists(Ice::getRootPath() . $moduleName)) {
            return [
                'moduleDir' => Dir::get(Ice::getRootPath() . $moduleName),
                'mainConfigFile' => $moduleName . '.conf.php',
                'message' => 'module' . $moduleName . 'already exists!'
            ];
        }

        // create module dir
        $moduleDir = Dir::get(Ice::getRootPath() . $moduleName);

        //copy index file
        copy(Ice::getEnginePath() . Ice::INDEX, $moduleDir . Ice::INDEX);

        //generate config
        $config = [
            'defaultLayoutView' => null,
            'defaultLayoutAction' => 'Ice:Layout_Main',
            'defaultViewRenderClass' => 'Ice:Php',
            'modules' => [
                $moduleName => $moduleDir,
                'Ice' => Ice::getEnginePath()
            ],
            'configs' => [
                $moduleName . '\core\Model' => [
                    $moduleName => $moduleName . '\Model\\' . $moduleName . '\\'
                ],
                'ice\core\Action' => [
                    $moduleName => $moduleName . '\Action\\',
                ],
            ],
            'hosts' => ['/' . strtolower($moduleName) . '.local$/' => 'development'],
            'environments' => ['production' => []],
            'viewRenders' => []
        ];

        File::createData($moduleDir . $moduleName . '.conf.php', $config);

        return [
            'moduleDir' => $moduleDir,
            'mainConfigFile' => $moduleName . '.conf.php',
            'message' => 'module' . $moduleName . 'was created!'
        ];
    }
}