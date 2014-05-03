<?php
namespace ice\core;

use ice\Exception;
use ice\helper\Json;
use ice\helper\Object;
use ice\helper\String;
use ice\Ice;

/**
 * Core class Query
 *
 * Query bulider
 *
 * @package ice\core
 * @author dp
 */
class Query
{
    const SQL_STATEMENT_SELECT = 'SELECT';
    const SQL_STATEMENT_INSERT = 'INSERT';
    const SQL_STATEMENT_UPDATE = 'UPDATE';
    const SQL_STATEMENT_DELETE = 'DELETE';
    const SQL_FUNCTION_COUNT = 'COUNT';
    const SQL_CLAUSE_FROM = 'FROM';
    const SQL_CLAUSE_INTO = 'INTO';
    const SQL_CLAUSE_SET = 'SET';
    const SQL_CLAUSE_VALUES = 'VALUES';
    const SQL_CLAUSE_INNER_JOIN = 'INNER JOIN';
    const SQL_CLAUSE_LEFT_JOIN = 'LEFT JOIN';
    const SQL_CLAUSE_KEYWORD_JOIN = 'JOIN';
    const SQL_CLAUSE_WHERE = 'WHERE';
    const SQL_CLAUSE_ORDER = 'ORDER';
    const SQL_CLAUSE_LIMIT = 'LIMIT';
    const CLAUSE_WHERE_LOGICAL_OPERATOR = 'lo';
    const CLAUSE_WHERE_FIELD_NAME = 'fn';
    const CLAUSE_WHERE_COMPARSION_OPERATOR = 'co';
    const CLAUSE_WHERE_FIELD_VALUE = 'fv';
    const SQL_LOGICAL_AND = 'AND';
    const SQL_LOGICAL_OR = 'OR';
    const SQL_LOGICAL_NOT = 'NOT';
    const SQL_COMPARSION_OPERATOR_EQUAL = '=';
    const SQL_COMPARSION_OPERATOR_NOT_EQUAL = '<>';
    const SQL_COMPARSION_OPERATOR_LESS = '<';
    const SQL_COMPARSION_OPERATOR_GREATER = '>';
    const SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL = '>=';
    const SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL = '<=';
    const SQL_COMPARSION_KEYWORD_LIKE = 'LIKE';
    const SQL_COMPARSION_KEYWORD_RLIKE = 'RLIKE';
    const SQL_COMPARSION_KEYWORD_RLIKE_REVERSE = 'RLIKE_REVERSE';
    const SQL_COMPARSION_KEYWORD_IN = 'IN';
    const SQL_COMPARSION_KEYWORD_NOT_IN = 'NOT IN';
    const SQL_COMPARSION_KEYWORD_BETWEEN = 'BETWEEN';
    const SQL_COMPARSION_KEYWORD_IS_NULL = 'IS NULL';
    const SQL_COMPARSION_KEYWORD_IS_NOT_NULL = 'IS NOT NULL';
    const SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';

    private $_parts = [];
    private $_result = null;
    private $_modelClass = null;
    private $_tableAlias = null;

    /**
     * Private constructor of query builder. Create: Query::getInstance()->...
     *
     * @param $statementType
     * @param $modelClass
     * @param $tableAlias
     */
    private function __construct($statementType, $modelClass, $tableAlias)
    {
        $this->_statementType = $statementType;
        $this->_modelClass = $modelClass;
        if (!$tableAlias) {
            $this->_tableAlias = $modelClass::getModelName();
        }
    }

    /**
     * Create instance for query builder
     *
     * @param $statementType
     * @param $modelClass
     * @param null $tableAlias
     * @return Query
     */
    public static function getInstance($statementType, $modelClass, $tableAlias = null)
    {
        return new Query($statementType, $modelClass, $tableAlias);
    }

    /**
     * Set data of query part select
     *
     * @param $fieldNames
     * @param null $alias
     * @return $this
     */
    public function select($fieldNames, $alias = null)
    {
        if (empty($fieldNames)) {
            return $this;
        }

        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        if ($fieldNames == '*') {
            $fieldNames = $modelClass::getFieldNames();
        }

        if (is_array($fieldNames)) {
            foreach ($fieldNames as $fieldName => $alias) {
                if (is_string($fieldName)) {
                    $this->select($fieldName, $alias);
                } else {
                    $this->select($alias);
                }
            }

            return $this;
        } else {
            $fields = explode(',', $fieldNames);
            if (count($fields) > 1) {
                $this->select($fields);
                return $this;
            }
        }

//        if (empty($this->_parts[Query::SQL_STATEMENT_SELECT])) {
//            $pkName = $modelClass::getPkName();
//            $this->_parts[Query::SQL_STATEMENT_SELECT][$pkName] = $pkName;
//        }

        $fieldNames = $modelClass::getFieldName($fieldNames);

        if (!$alias) {
            $alias = $fieldNames;
        }

        $this->_parts[Query::SQL_STATEMENT_SELECT][$alias] = $fieldNames;

        return $this;
    }

    /**
     * Return model class for query
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * Execute current query
     *
     * @param Data_Source $dataSource
     * @return Data
     */
    public function execute(Data_Source $dataSource = null)
    {
        return $this->getDataSource($dataSource)->execute($this);
    }

    /**
     * Get data source for current query
     *
     * @param Data_Source $dataSource
     * @return Data_Source
     */
    private function getDataSource(Data_Source $dataSource = null)
    {
        if (!$dataSource) {
            /** @var Model $modelName */
            $modelName = $this->getModelClass();

            $dataSource = $modelName::getDataSource();
        }

        return $dataSource;
    }

    /**
     * Set in query part where expression 'IS NOT NULL'
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function notNull($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL
        );
    }

    /**
     * Set data in query part where
     *
     * @param $sqlLogical
     * @param $fieldName
     * @param $sql_comparsion
     * @param null $value
     * @return $this
     * @throws Exception
     */
    private function where($sqlLogical, $fieldName, $sql_comparsion, $value = null)
    {
        if ($this->_result !== null) {
            throw new Exception('Запрос уже оттранслирован ранее. Внесение изменений в запрос не принесет никаких результатов');
        }

        $where = [
            [
                Query::CLAUSE_WHERE_LOGICAL_OPERATOR => $sqlLogical,
                Query::CLAUSE_WHERE_FIELD_NAME => $fieldName,
                Query::CLAUSE_WHERE_COMPARSION_OPERATOR => $sql_comparsion
            ],
            $value
        ];

        $this->_parts[Query::SQL_CLAUSE_WHERE][] = $where;

        return $this;
    }

    /**
     * Set in query part where expression 'IS NULL'
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function isNull($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where($sqlLogical, $modelClass::getFieldName($fieldName), Query::SQL_COMPARSION_KEYWORD_IS_NULL);
    }

    /**
     * Set in query part where expression '= ?' for primary key column
     *
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function pk($value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq('/pk', $value, $sqlLogical);
    }

    /**
     * Set in query part where expression '= ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function eq($fieldName, $value = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $key => $value) {
                $this->eq($key, $value, $sqlLogical);
            }

            return $this;
        }

        if (is_array($value)) {
            return $this->in($fieldName, $value, $sqlLogical);
        }

        if ($value instanceof Model) {
            $value = $value->getPk();
            $fieldName .= '__fk';
        }

        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_EQUAL,
            $value
        );
    }

    /**
     * Set in query part where expression 'in (?)'
     *
     * @param $fieldName
     * @param array $value
     * @param string $sqlLogical
     * @return $this
     */
    public function in($fieldName, array $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (empty($value)) {
            return $this;
        }

        if (count($value) == 1) {
            return $this->eq($fieldName, reset($value), $sqlLogical);
        }

        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_KEYWORD_IN,
            $value
        );
    }

    /**
     * Set in query part where expression '>= ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function ge($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL,
            $value
        );
    }

    /**
     * Set in query part where expression '<= ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function le($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL,
            $value
        );
    }

    /**
     * Set in query part where expression '> ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function gt($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_GREATER,
            $value
        );
    }

    /**
     * Set in query part where expression '< ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function lt($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_LESS,
            $value
        );
    }

    /**
     * Set in query part where expression '= ""'
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function isEmpty($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, '', $sqlLogical);
    }

    /**
     * Set in query part where expression '<> ""'
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function notEmpty($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->ne($fieldName, '', $sqlLogical);
    }

    /**
     * Set in query part where expression '<> ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function ne($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL,
            $value
        );
    }

    /**
     * Set in query part where expression 'not in (?)'
     *
     * @param $fieldName
     * @param array $value
     * @param string $sqlLogical
     * @return $this
     */
    public function notIn($fieldName, array $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (empty($value)) {
            return $this;
        }

        if (count($value) == 1) {
            return $this->eq($fieldName, reset($value), $sqlLogical);
        }

        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_KEYWORD_NOT_IN,
            $value
        );
    }

    /**
     * Set in query part where expression '== ?' is boolean true(1)
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function is($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, 1, $sqlLogical);
    }

    /**
     * Set in query part where expression '== ?' is boolean false(0)
     *
     * @param $fieldName
     * @param string $sqlLogical
     * @return $this
     */
    public function not($fieldName, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, 0, $sqlLogical);
    }

    /**
     * Set in query part where expression 'like ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function like($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        return $this->where(
            $sqlLogical,
            $modelClass::getFieldName($fieldName),
            Query::SQL_COMPARSION_KEYWORD_LIKE,
            $value
        );
    }

    /**
     * Set in query part where expression 'rlike ?'
     *
     * @param $fieldName
     * @param $value
     * @param string $sqlLogical
     * @return $this
     */
    public function rlike($fieldName, $value, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();
        $modelFields = $modelClass::getMapping()->getFieldNames();
        $fieldValue = $modelClass::getFieldName($value);

        /** check ability use pattern from field in base */
        return array_key_exists($fieldValue, $modelFields)
            ? $this->where($sqlLogical, $fieldValue, Query::SQL_COMPARSION_KEYWORD_RLIKE_REVERSE, $fieldName)
            : $this->where($sqlLogical, $modelClass::getFieldName($fieldName), Query::SQL_COMPARSION_KEYWORD_RLIKE, $value);
    }

    /**
     * Set inner join query part
     *
     * @param $modelClass
     * @return $this
     */
    public function inner($modelClass)
    {
        return $this->join(Query::SQL_CLAUSE_INNER_JOIN, $modelClass);
    }

    /**
     * Set  *join query part
     *
     * @param $joinType
     * @param $modelClass
     * @param null $tableAlias
     * @param null $condition
     * @return $this
     * @throws Exception
     */
    private function join($joinType, $modelClass, $tableAlias = null, $condition = null)
    {
        if ($this->_result !== null) {
            throw new Exception('Запрос уже оттранслирован ранее. Внесение изменений в запрос не принесет никаких результатов');
        }

        if (!$tableAlias) {
            $tableAlias = Object::getName($modelClass);
        }

        $currentJoin = [
            'type' => $joinType,
            'class' => $modelClass,
            'alias' => $tableAlias
        ];

        if (!$condition) {
            $modelName = Object::getName($modelClass);
            $modelMappingFieldNames = $modelClass::getMapping()->getFieldNames();
            $modelMappingFieldNamesOnly = array_keys($modelMappingFieldNames);

            $joins = [['class' => $this->getModelClass(), 'alias' => $this->getTableAlias()]];

            if (!empty($this->getJoin())) {
                $joins = array_merge($joins, $this->getJoin());
            }

            $joins[] = $currentJoin;

            foreach ($joins as $join) {
                /** @var Model $joinModelClass */
                $joinModelClass = $join['class'];
                $joinTableAlias = $join['alias'];

                $joinModelMappingFieldNames = $joinModelClass::getMapping()->getFieldNames();
                $joinModelMappingFieldNamesOnly = array_keys($joinModelMappingFieldNames);

                $joinModelName = Object::getName($joinModelClass);

                $joinModelNameFk = strtolower($joinModelName . '__fk');
                $joinModelNamePk = strtolower($joinModelName) . '_pk';

                if (in_array($joinModelNameFk, $modelMappingFieldNamesOnly)) {
                    $condition = $tableAlias . '.' . $modelMappingFieldNames[$joinModelNameFk] . ' = ' .
                        $joinTableAlias . '.' . $joinModelMappingFieldNames[$joinModelNamePk];
                    break;
                }

                $modelNameFk = strtolower($modelName . '__fk');
                $modelNamePk = strtolower($modelName) . '_pk';

                if (in_array($modelNameFk, $joinModelMappingFieldNamesOnly)) {
                    $condition = $tableAlias . '.' . $modelMappingFieldNames[$modelNamePk] . ' = ' .
                        $joinTableAlias . '.' . $joinModelMappingFieldNames[$modelNameFk];
                    break;
                }
            }

            if (!$condition) {
                throw new Exception('Could not defined condition for join part of sql query', $this->_parts);
            }
        }

        $currentJoin['on'] = $condition;

        $this->_parts[Query::SQL_CLAUSE_KEYWORD_JOIN][] = $currentJoin;

        return $this;
    }

    /**
     * Return alias for table of model class of this query
     *
     * @return string
     */
    public function getTableAlias()
    {
        return $this->_tableAlias;
    }

    /**
     * Return join parts of this query
     *
     * @return array
     */
    public function getJoin()
    {
        return isset($this->_parts[Query::SQL_CLAUSE_KEYWORD_JOIN]) ? $this->_parts[Query::SQL_CLAUSE_KEYWORD_JOIN] : [];
    }

    /**
     * Set inner join query part
     *
     * @param $modelClass
     * @return $this
     */
    public function left($modelClass)
    {
        return $this->join(Query::SQL_CLAUSE_LEFT_JOIN, $modelClass);
    }

    /**
     * Set data for values query part of insert
     *
     * @param array $values
     * @return Query
     */
    public function values(array $values)
    {
        if (!isset($this->_parts[Query::SQL_CLAUSE_VALUES])) {
            $this->_parts[Query::SQL_CLAUSE_VALUES] = [];
        }

        if (is_array(reset($values))) {
            $this->_parts[Query::SQL_CLAUSE_VALUES] += $values;
        } else {
            $this->_parts[Query::SQL_CLAUSE_VALUES][] = $values;
        }

        return $this;
    }

    /**
     * Set data for set query part of update
     *
     * @param $key
     * @param null $value
     * @return $this
     * @throws Exception
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }

            return $this;
        }

        if (empty($key)) {
            throw new Exception('Имя поля не может быть пустым');
        }

        $this->_parts[Query::SQL_CLAUSE_SET][$key] = $value;

        return $this;
    }

    /**
     * Get data of query part select
     *
     * @return array
     */
    public function getSelect()
    {
        return isset($this->_parts[Query::SQL_STATEMENT_SELECT]) ? $this->_parts[Query::SQL_STATEMENT_SELECT] : [];
    }

    /**
     * Get data of query part where
     *
     * @return array
     */
    public function getWhere()
    {
        return isset($this->_parts[Query::SQL_CLAUSE_WHERE]) ? $this->_parts[Query::SQL_CLAUSE_WHERE] : [];
    }

    /**
     * Get data of query part values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_parts[Query::SQL_CLAUSE_VALUES];
    }

    /**
     * Get data of query part set
     *
     * @return array
     */
    public function getSet()
    {
        return $this->_parts[Query::SQL_CLAUSE_SET];
    }

    /**
     * Casts query to string
     *
     * @return string
     */
    public function __toString()
    {
        return print_r($this->translate(), true);
    }

    /**
     * Get translated query and binds values
     *
     * @param string $dataSourceName
     * @return array
     */
    public function translate($dataSourceName = 'Mysqli')
    {
        if ($this->_result !== null) {
            return $this->_result;
        }

        $statementType = $this->getStatementType();
        $queryTranslator = Query_Translator::getInstance('\ice\query\translator\\' . $dataSourceName);

        if ($statementType != strtolower(Query::SQL_STATEMENT_SELECT)) {
            return $queryTranslator->translate($this);
        }

        $queryCacheDataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));
        $queryHash = md5(Json::encode($this->getParts()));
        $translateResultJson = $queryCacheDataProvider->get($queryHash);

        if (!empty($translateResultJson)) {
            return $translateResultJson;
        }

        $this->_result = $queryTranslator->translate($this);
        $queryCacheDataProvider->set($queryHash, $this->_result, 0);

        return $this->_result;
    }

    /**
     * Get statment type of current query (SELECT, INSERT, UPDATE, DELETE, SHOW, CREATE_TABLE etc.)
     *
     * @return string
     */
    public function getStatementType()
    {
        return $this->_statementType;
    }

    /**
     * Return all query parts
     *
     * @return array
     */
    public function getParts()
    {
        return $this->_parts;
    }

    /**
     * Set flag of get count rows
     *
     * @param $fieldName
     * @return $this
     */
    public function count($fieldName = null)
    {
        if (!$fieldName) {
            /** @var Model $modelClass */
            $modelClass = $this->getModelClass();
            $fieldName = $modelClass::getPkName();
        }

        $this->_parts[Query::SQL_FUNCTION_COUNT] = $fieldName;
        return $this;
    }

    public function calcFoundRows()
    {
        $this->_parts[Query::SQL_CALC_FOUND_ROWS] = true;
        return $this;
    }

    /**
     * Check flag is select count
     *
     * @return boolean
     */
    public function getSelectCount()
    {
        return isset($this->_parts[Query::SQL_FUNCTION_COUNT]) ? $this->_parts[Query::SQL_FUNCTION_COUNT] : null;
    }

    /**
     * Ascending ordering
     *
     * @param $fieldName
     * @return $this
     */
    public function asc($fieldName)
    {
        return $this->order($fieldName, 'ASC');
    }

    /**
     * Ordering
     *
     * @param $fieldName
     * @param $isAscending
     * @return $this
     */
    private function order($fieldName, $isAscending)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $name) {
                $this->order($name, $isAscending);
            }

            return $this;
        }

        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();

        $this->_parts[Query::SQL_CLAUSE_ORDER][$modelClass::getFieldName($fieldName)] = $isAscending;

        return $this;
    }

    /**
     * Descending ordering
     *
     * @param $fieldName
     * @return $this
     */
    public function desc($fieldName)
    {
        return $this->order($fieldName, 'DESC');
    }

    /**
     * Get data of query part order by
     *
     * @return array
     */
    public function getOrder()
    {
        return isset($this->_parts[Query::SQL_CLAUSE_ORDER]) ? $this->_parts[Query::SQL_CLAUSE_ORDER] : [];
    }

    public function getHashParts()
    {
        return crc32(String::serialize($this->getParts()));
    }

    public function getHashBinds()
    {
        return crc32(String::serialize($this->getBinds()));
    }

    public function getBinds()
    {
        return []; // todo: implements
    }

    public function isCalcFoundRows()
    {
        return isset($this->_parts[Query::SQL_CALC_FOUND_ROWS]) &&
        $this->_parts[Query::SQL_CALC_FOUND_ROWS] === true &&
        !empty($this->getLimit());
    }

    /**
     * Get data of query part limit
     *
     * @return array
     */
    public function getLimit()
    {
        return isset($this->_parts[Query::SQL_CLAUSE_LIMIT]) ? $this->_parts[Query::SQL_CLAUSE_LIMIT] : [];
    }

    public function setPaginator($page = 1, $limit = 1000)
    {
        $this->limit(1000, ($page - 1) * $limit);
        return $this;
    }

    /**
     * Set query part limit
     *
     * @param $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        $this->_parts[Query::SQL_CLAUSE_LIMIT] = [$limit, $offset];
        return $this;
    }
}