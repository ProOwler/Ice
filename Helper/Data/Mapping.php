<?php
namespace ice\helper;

use ice\core\Config;
use ice\core\Data_Source;

class Data_Mapping
{
    public static function getTableNameByClass($modelClass)
    {
        return \ice\core\Data_Mapping::getInstance()->getModelClasses()[$modelClass];
    }

    public static function syncConfig() {
        $dataSchemeTableNames = array_keys(Data_Source::getDefault()->getDataScheme());
        $dataMappingConfigData = [];

        foreach (array_diff($dataSchemeTableNames, array_values($dataMappingConfigData)) as $tableName) {
            $dataMappingConfigData[Model::tableToModel($tableName)] = $tableName;
        }

        return Config::create(\ice\core\Data_Mapping::getClass(), $dataMappingConfigData);
    }
}