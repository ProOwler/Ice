<?php
namespace ice\core;

use ice\helper\Json;
use ice\Ice;

abstract class Query_Translator
{
    abstract protected function select(Query &$query);

    abstract protected function insert(Query &$query);

    abstract protected function update(Query &$query);

    abstract protected function delete(Query &$query);

    public function translate(Query &$query)
    {
        $statementType = $query->getStatementType();
       return $this->$statementType($query);
    }

    /**
     * @param $className
     * @return Query_Translator
     */
    public static function getInstance($className)
    {
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));
        $queryTranslator = $dataProvider->get($className);
        if ($queryTranslator) {
            return $queryTranslator;
        }
        $queryTranslator = new $className();
        $dataProvider->set($className, $queryTranslator);
        return $queryTranslator;
    }
}