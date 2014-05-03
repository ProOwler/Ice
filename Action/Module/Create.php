<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 03.05.14
 * Time: 12:26
 */

namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\Action_Context;
use ice\helper\Dir;
use ice\Ice;

class Module_Create extends Action implements Cli {

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
        $moduleDir = Dir::get(Ice::getRootPath() . $input['name']);

        var_dump($moduleDir);
        echo 'module created!';
    }
}