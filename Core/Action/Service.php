<?php
namespace ice\core\action;

use ice\core\Action;
use ice\Exception;

/**
 * Cli Service Action
 *
 * @package ice\core\action
 * @author dp <denis.a.shestakov@gmail.com>
 */
abstract class Service extends Action implements Cli, Factory
{
    const STATUS_UNKNOWN = 0; // сервис должен быть остановлен
    const STATUS_ON = 1; // сервис должен быть запущен, но пока остановлен
    const STATUS_RUN = 2; // сервис в данный момент работает
    const STATUS_OFF = 3; // сервис должен быть остановлен

    const KEY_STATUS = 'service_status';

    public static function getDelegate($delegateName)
    {
        $actionServiceName = 'Action_' . $delegateName . '_Service';
        return $actionServiceName::get();
    }

    public function isRunStatus()
    {
        $status = $this->getStatus();
        if ($status == Action_Service::STATUS_OFF) {
            return false;
        }

        if ($status != Action_Service::STATUS_RUN) {
            $this->setStatus(Action_Service::STATUS_RUN);
        }

        return true;
    }

    public function getStatus()
    {
        return $this->getDataProvider()->get(self::KEY_STATUS);
    }

    protected function getDataProvider()
    {
        if ($this->_dataProvider !== null) {
            return $this->_dataProvider;
        }

        $this->_dataProvider = Data_Provider_Manager::get($this->getDataProviderName());

        return $this->_dataProvider;
    }

    protected function getDataProviderName()
    {
        return $this->getConfig()->get('dataProviderName');
    }

    public function setStatus($status)
    {
        $this->getDataProvider()->set(self::KEY_STATUS, $status);
    }

    protected function getActionService(array &$input)
    {
        if (!isset($input['name'])) {
            throw new Exception('Необходимо передать имя --name сервиса');
        }

        /** @var Action_Service $actionService */
        $actionService = Action_Service::getDelegate($input['name']);
        unset($input['name']);

        if (!$actionService) {
            throw new Exception('Сервис "' . $input['name'] . '" не найден');
        }

        unset($input['name']);

        return $actionService;
    }
}