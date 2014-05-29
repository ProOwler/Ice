<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 23.05.14
 * Time: 16:40
 */

namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\Action_Context;
use ice\core\Config;
use ice\helper\Date;
use ice\Ice;

class Migration_Create extends Action implements Cli
{

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        var_dump($input);die();

        $actionMigrationClass = Migration::getClass();
        $projectName = Ice::getProject();
        $config = Config::getInstance($actionMigrationClass);

        $list = null;

        if ($config) {
            $list = $config->get($projectName, false);
        }

        if (!$list) {
            $list = [
                $projectName => []
            ];
        }

        $migration = [
            'moduleName' => [
                Date::getCurrent() => [
                    'Simple' => 'createTableUser',
                    'Add_User',

                ]
            ]
        ];

        $list[$projectName][''] = [

        ];

        var_dump(Config::create($actionMigrationClass, $list, null, true));
    }
}