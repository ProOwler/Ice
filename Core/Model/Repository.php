<?php
namespace ice\core;

final class Model_Repository
{
    const DATA_PROVIDER_KEY = 'Registry:model_repository/';

    private $_dataProvider = null;

    private function __construct()
    {
        $this->_dataProvider = Data_Provider::getInstance(Model_Repository::DATA_PROVIDER_KEY);
    }

    public static function get($scheme, $pk)
    {
        $dataProvider = self::getInstance()->_dataProvider;
        $dataProvider->setScheme($scheme);
        return $dataProvider->get($pk);
    }

    /**
     * @return Model_Repository
     */
    public static function getInstance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Model_Repository();
        }
        return $inst;
    }

    public static function set($scheme, $pk, $model)
    {
        $dataProvider = self::getInstance()->_dataProvider;
        $dataProvider->setScheme($scheme);
        return $dataProvider->set($pk, $model);
    }
}