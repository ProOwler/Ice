<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\core\Config;
use ice\core\Route;
use ice\data\provider\Request;
use ice\data\provider\Router;
use ice\helper\Dir;
use ice\Ice;
use ice\view\render\Php;

/**
 * Class Main. Hello world action.
 *
 * First step of creating module
 *
 * @see \ice\core\Action
 * @see \ice\core\action\View
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Main extends Action implements View
{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;
    protected $dataProviderKeys = Request::DEFAULT_KEY;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        if (isset($input['install'])) {
            $dirs = [
                'Action/Layout',
                'Config/Ice/Core',
                'Model',
                'Resource/js',
                'Resource/css',
                'Resource/img',
                'Resource/Vendor',
                'View/Template'
            ];

            $modulePath = Ice::getProjectPath();

            $isNeedInstall = true;

            foreach ($dirs as $dir) {
                if (is_dir($modulePath . $dir)) {
                    $isNeedInstall = false;
                    break;
                }
            }

            if ($isNeedInstall) {
                foreach ($dirs as $dir) {
                    Dir::get($modulePath . $dir);
                }

                Create::call(['name' => 'Db:Index', 'interfaces' => 'View'])->display();

                Config::create(Route::getClass(), [
                        [
                            'route' => '/',
                            'actions' => [
                                'title' => ['Ice:Title' => ['title' => Ice::getProject()]],
                                'main' => 'Db:Index'
                            ]
                        ]
                    ]
                );
            }
        }

        return [
            'install' => isset($input['install']),
            'welcome' => 'Hello world',
            'enjoy' => 'Ice is Great!!!',
            'project' => Ice::getProject()
        ];
    }
}