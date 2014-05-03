<?php

namespace ice\action;

use ice\core\action\Service;
use ice\core\Action_Context;

/**
 * Show status service action
 *
 * @package ice\action
 * @author dp
 */
class Service_Status extends Service
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
            case Service::STATUS_ON:
                echo 'Сервис "' . $actionService->getClassName() . '" запускается' . "\n";
                break;
            case Service::STATUS_RUN:
                echo 'Сервис "' . $actionService->getClassName() . '" запущен и работает' . "\n";
                break;
            case Service::STATUS_OFF:
                echo 'Сервис "' . $actionService->getClassName() . '" остановлен' . "\n";
                break;
            default:
                echo 'Статус сервиса "' . $actionService->getClassName() . '" не определен' . "\n";
                break;
        }
    }
}