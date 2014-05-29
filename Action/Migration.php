<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 23.05.14
 * Time: 15:39
 */

namespace ice\action;

use ice\core\Action;
use ice\core\Action_Context;
use ice\core\Config;

class Migration extends Action
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
        $config = $this->getConfig();

        if (!$config) {
            return;
        }

        foreach ($config->gets() as $moduleName => $data) {
            foreach ($data as $moduleVersion => $migration) {

            }
        }
    }
}