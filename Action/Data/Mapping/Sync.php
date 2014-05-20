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
 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
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