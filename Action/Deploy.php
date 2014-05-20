<?php
namespace ice\action;

use ice\core\action\Cli;
use ice\core\Action;
use ice\core\Action_Context;
use ice\Exception;

/**
 * Deploy ice application
 *
 * @see \ice\core\Action
 * @see \ice\core\action\Cli
 *
 * @package ice\action
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Deploy extends Action implements Cli
{
    protected $staticActions = [
        'ice\action\Data_Mapping_Sync',
        'ice\action\Model_Scheme_Sync',
        'ice\action\Model_Defined_Sync'
    ];

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @throws Exception
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        throw new Exception('Implement run() method.');
    }
}