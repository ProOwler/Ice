<?php
namespace ice\core;

use ice\helper\Object;
use ice\Ice;

abstract class Query_Translator
{
    /**
     * @param null $name
     * @return Query_Translator
     */
    public static function getInstance($name = null)
    {
        /** @var Validator $class */
        $className = $name
            ? Object::getClassByClassShortName(__CLASS__, $name)
            : get_called_class();

        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));
        $queryTranslator = $dataProvider->get($className);
        if ($queryTranslator) {
            return $queryTranslator;
        }
        $queryTranslator = new $className();
        $dataProvider->set($className, $queryTranslator);
        return $queryTranslator;
    }

    abstract public function translate(Query $query);
}