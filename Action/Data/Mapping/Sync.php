<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\Cli;
use ice\core\Action_Context;

/**
 * Data mapping syncronization
 *
 * Mapping of classes and table names
 *
 * @package ice\action
 * @author dp
 */
class Data_Mapping_Sync extends Action implements Cli
{
    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        \ice\helper\Data_Mapping::syncConfig();
    }
}