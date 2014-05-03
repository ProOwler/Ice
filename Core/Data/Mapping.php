<?php
namespace ice\core;

use ice\helper\Object;
use ice\Ice;

class Data_Mapping
{
    const DATA_PROVIDER_KEY = 'dataMappingDataProviderKey';

    /** @var Config */
    private $_dataMappingConfig = null;

    private function __construct(Config $dataMappingConfig)
    {
        $this->_dataMappingConfig = $dataMappingConfig;
    }

    public static function add($modelClass)
    {
        $dataMappingConfigData = Data_Mapping::getInstance()->getModelClasses();

        if (isset($dataMappingConfigData[$modelClass])) {
            return;
        }

        $table = strtolower(Object::getName($modelClass));
        $namespace = substr($modelClass, 0, strrpos($modelClass, '\\'));
        $prefix = substr($namespace, strrpos($namespace, '\\') + 1) . '_';

        $dataMappingConfigData[$modelClass] = $prefix . $table;

        $dataMapping = new Data_Mapping(Config::create(__CLASS__, $dataMappingConfigData));

        if ($dataMapping) {
            Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__))
                ->set(__CLASS__, $dataMapping);
        }
    }

    public function getModelClasses()
    {
        return (array)$this->_dataMappingConfig->gets();
    }

    /**
     * Return array mapping classes of models and their table names
     *
     * @return Data_Mapping
     */
    public static function getInstance()
    {
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $dataMapping = $dataProvider->get(__CLASS__);

        if ($dataMapping) {
            return $dataMapping;
        }

        $dataMappingConfig = Config::getInstance(__CLASS__);

        $dataMapping = $dataMappingConfig
            ? new Data_Mapping($dataMappingConfig)
            : Data_Mapping::create();

        if ($dataMapping) {
            $dataProvider->set(__CLASS__, $dataMapping);
        }

        return $dataMapping;
    }

    private static function create()
    {
        return new Data_Mapping(\ice\helper\Data_Mapping::syncConfig());
    }

    public static function getClass()
    {
        return get_called_class();
    }
}