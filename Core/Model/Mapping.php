<?php
namespace ice\core;

use ice\Ice;

class Model_Mapping
{
    const POSTFIX = 'Mapping';

    /** @var Config */
    private $_modelMappingConfig = null;

    private function __construct(Config $modelMappingConfig)
    {
        $this->_modelMappingConfig = $modelMappingConfig;
    }

    public static function getInstance($modelClass)
    {
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $modelMapping = $dataProvider->get($modelClass);

        if ($modelMapping) {
            return $modelMapping;
        }

        $modelMappingConfig = Config::getInstance($modelClass, [], self::POSTFIX);

        $modelMapping = $modelMappingConfig
            ? new Model_Mapping($modelMappingConfig)
            : Model_Mapping::create($modelClass);

        if ($modelMapping) {
            $dataProvider->set($modelClass, $modelMapping);
        }

        return $modelMapping;
    }

    private static function create($modelClass)
    {
        $columnNames = $modelClass::getScheme()->getColumnNames();

        $modelSchemeConfigData = [];

        foreach ($columnNames as $columnName) {
            $modelSchemeConfigData[$columnName] = $columnName;
        }

        $modelMappingConfig = Config::create($modelClass, $modelSchemeConfigData, self::POSTFIX);

        return new Model_Mapping($modelMappingConfig);
    }

    public function getFieldNames()
    {
        return $this->_modelMappingConfig->gets();
    }
}