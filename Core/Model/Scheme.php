<?php
namespace ice\core;

use ice\Ice;

class Model_Scheme
{
    /** @var Config */
    private $_modelSchemeConfig = null;

    private function __construct(Config $modelSchemeConfig)
    {
        $this->_modelSchemeConfig = $modelSchemeConfig;
    }

    public static function getInstance($modelClass)
    {
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $modelScheme = $dataProvider->get($modelClass);

        if ($modelScheme) {
            return $modelScheme;
        }

        $modelSchemeConfig = Config::getInstance($modelClass, [], 'Scheme');

        $modelScheme = $modelSchemeConfig
            ? new Model_Scheme($modelSchemeConfig)
            : Model_Scheme::create($modelClass);

        if ($modelScheme) {
            $dataProvider->set($modelClass, $modelScheme);
        }

        return $modelScheme;
    }

    private static function create($modelClass)
    {
        $dataMapping = Data_Mapping::getInstance();
        $dataMapping->add($modelClass);

        $tableName = Data_Mapping::getInstance()->getModelClasses()[$modelClass];
        $modelSchemeConfigData = Data_Source::getDefault()->getDataScheme()[$tableName];

        $modelSchemeConfig = Config::create($modelClass, $modelSchemeConfigData, 'Scheme');

        return new Model_Scheme($modelSchemeConfig);

    }

    public function getColumnNames()
    {
        return array_keys($this->getColumns());
    }

    public function getColumns()
    {
        return $this->_modelSchemeConfig->gets();
    }
}