<?php

namespace ice\action;

use ice\core\action\Service;
use ice\core\Action_Context;

/**
 * Stop action service
 *
 * @package ice\action
 * @author dp
 */
class Service_Stop extends Service
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
        $actionService = $this->getActionService($input);

        switch ($actionService->getStatus()) {
            case Service::STATUS_OFF:
                echo 'Сервис "' . $actionService->getClassName() . '" уже остановлен' . "\n";
                break;
            default:
                $actionService->setStatus(Service::STATUS_OFF);
                echo 'Сервису "' . $actionService->getClassName() . '" послан сигнал для останова' . "\n";
        }
    }
}