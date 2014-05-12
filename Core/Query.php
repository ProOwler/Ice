<?php
namespace ice\core;

use ice\Exception;
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
class Query implements Cacheable
{
    const TYPE_SELECT = 'select';
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const PART_SELECT = 'select';
    const PART_VALUES = 'values';
    const PART_SET = 'set';
    const PART_JOIN = 'join';
    const PART_WHERE = 'where';
    const PART_ORDER = 'order';
    const PART_LIMIT = 'limit';
    const SQL_CLAUSE_INNER_JOIN = 'INNER JOIN';
    const SQL_CLAUSE_LEFT_JOIN = 'LEFT JOIN';
    const SQL_CLAUSE_KEYWORD_JOIN = 'JOIN';
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

    private $_sqlParts = [
        self::PART_SELECT => [
            '_calcFoundRows' => null,
        ],
        self::PART_VALUES => [],
        self::PART_SET => [],
        self::PART_JOIN => [],
        self::PART_WHERE => [
            '_delete' => null,
        ],
        self::PART_ORDER => [],
        self::PART_LIMIT => []
    ];

    private $_bindParts = [
        self::PART_VALUES => [],
        self::PART_SET => [],
        self::PART_WHERE => [],
        self::PART_ORDER => [],
        self::PART_LIMIT => []
    ];

    private $_cacheTags = [
        'validate' => [],
        'invalidate' => []
    ];

    private $_sql = null;
    private $_binds = null;

    private $_queryType = null;
    private $_modelClass = null;
    private $_tableAlias = null;

    private $_dataSource = null;

    /**
     * Private constructor of query builder. Create: Query::getInstance()->...
     *
     * @param $queryType
     * @param $modelClass
     * @param $tableAlias
     */
    private function __construct($queryType, $modelClass, $tableAlias)
    {
        $this->_queryType = $queryType;
        $this->_modelClass = $modelClass;
        $this->_tableAlias = $tableAlias;

        if ($queryType == self::TYPE_DELETE) {
            $this->_sqlParts[self::PART_WHERE]['_delete'] = $modelClass;
        }
    }

    /**
     * Create instance for query builder
     *
     * @param $queryType
     * @param $modelClass
     * @param $tableAlias
     * @return Query
     */
    public static function getInstance($queryType, $modelClass, $tableAlias)
    {
        if (!$tableAlias) {
            $tableAlias = Object::getName($modelClass);
        }

        return new Query($queryType, $modelClass, $tableAlias);
    }

    /**
     * @param Data_Source $dataSource
     * @param bool $isUseCache
     * @throws Exception
     * @return Data
     */
    public function execute(Data_Source $dataSource = null, $isUseCache = true)
    {
        $this->_dataSource = $dataSource;

        $queryType = $this->getQueryType();

        if ($queryType == Query::TYPE_SELECT && !$isUseCache) {
            return new Data($this->getDataSource()->$queryType($this));
        }

        return new Data($this->getCache($this)['data']);
    }

    /**
     * Get data source for current query
     *
     * @return Data_Source
     */
    private function getDataSource()
    {
        if ($this->_dataSource !== null) {
            return $this->_dataSource;
        }

        /** @var Model $modelName */
        $modelName = $this->getModelClass();
        $this->_dataSource = $modelName::getDataSource();

        return $this->_dataSource;
    }

    /**
     * Return model class for query
     *
     * @return Model
     */
    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * Set in query part where expression 'IS NOT NULL'
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function notNull($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL,
            null,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set data in query part where
     *
     *  $_sqlPart[self::PART_WHERE] = [
     *      $modelClass => [
     *          $tableAlias, [
     *              [
     *                  Query::CLAUSE_WHERE_LOGICAL_OPERATOR => $sqlLogical,
     *                  Query::CLAUSE_WHERE_FIELD_NAME => $fieldName,
     *                  Query::CLAUSE_WHERE_COMPARSION_OPERATOR => $sql_comparsion
     *              ]
     *          ]
     *      ]
     *  ];
     *
     * @param $sqlLogical
     * @param $fieldName
     * @param $sqlComparsion
     * @param null $value
     * @param $modelClass
     * @param $tableAlias
     * @throws Exception
     * @return $this
     */
    private function where($sqlLogical, $fieldName, $sqlComparsion, $value = null, $modelClass = null, $tableAlias = null)
    {
        if ($this->_sql !== null) {
            throw new Exception('Запрос уже оттранслирован ранее. Внесение изменений в запрос не принесет никаких результатов');
        }

        if (!$modelClass) {
            $modelClass = $this->getModelClass();
        }

        if (!$tableAlias) {
            $tableAlias = $modelClass == $this->getModelClass()
                ? $this->getTableAlias()
                : Object::getName($modelClass);
        }

        $fieldName = $modelClass::getFieldName($fieldName);

        $where = [$sqlLogical, $fieldName, $sqlComparsion, count((array)$value)];

        if (isset($this->_sqlParts[self::PART_WHERE][$modelClass])) {
            $this->_sqlParts[self::PART_WHERE][$modelClass][1][] = $where;
        } else {
            $this->_sqlParts[self::PART_WHERE][$modelClass] = [
                $tableAlias, [$where]
            ];
        }

        $this->appendCacheTag($modelClass, $fieldName, true, false);

        $this->_bindParts[self::PART_WHERE][] = (array)$value;

        return $this;
    }

    /**
     * Return table alias for model class for query
     *
     * @return Model
     */
    public function getTableAlias()
    {
        return $this->_tableAlias;
    }

    private function appendCacheTag($modelClass, $fieldNames, $isValidate, $isInvalidate)
    {
        $modelMapping = $modelClass::getMapping()->getFieldNames();

        foreach ((array)$fieldNames as $fieldName) {
            if (array_key_exists($fieldName, $modelMapping)) {
                if ($isValidate) {
                    $this->_cacheTags['validate'][$modelClass][$fieldName] = true;
                }
                if ($isInvalidate) {
                    $this->_cacheTags['invalidate'][$modelClass][$fieldName] = true;
                }
            }
        }
    }

    /**
     * Set in query part where expression 'IS NULL'
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function isNull($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_KEYWORD_IS_NULL,
            null,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '= ?' for primary key column
     *
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @throws Exception
     * @return $this
     */
    public function pk($value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (empty($value)) {
            throw new Exception('Primary key is empty');
        }

        return $this->eq('/pk', $value, $modelClass, $tableAlias, $sqlLogical);
    }

    /**
     * Set in query part where expression '= ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function eq($fieldName, $value = null, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $key => $value) {
                $this->eq($key, $value, $modelClass, $tableAlias, $sqlLogical);
            }

            return $this;
        }

        if (is_array($value)) {
            return $this->in($fieldName, $value, $modelClass, $tableAlias, $sqlLogical);
        }

        if ($value instanceof Model) {
            $value = $value->getPk();
            $fieldName .= '__fk';
        }

        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_EQUAL,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression 'in (?)'
     *
     * @param $fieldName
     * @param array $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function in($fieldName, array $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (empty($value)) {
            return $this;
        }

        if (count($value) == 1) {
            return $this->eq($fieldName, reset($value), $modelClass, $tableAlias, $sqlLogical);
        }

        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_KEYWORD_IN,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '>= ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function ge($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '<= ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function le($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '> ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function gt($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_GREATER,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '< ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function lt($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_LESS,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '= ""'
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function isEmpty($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, '', $modelClass, $tableAlias, $sqlLogical);
    }

    /**
     * Set in query part where expression '<> ""'
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function notEmpty($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->ne($fieldName, '', $modelClass, $tableAlias, $sqlLogical);
    }

    /**
     * Set in query part where expression '<> ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function ne($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression 'not in (?)'
     *
     * @param $fieldName
     * @param array $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function notIn($fieldName, array $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (empty($value)) {
            return $this;
        }

        if (count($value) == 1) {
            return $this->eq($fieldName, reset($value), $modelClass, $tableAlias, $sqlLogical);
        }

        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_KEYWORD_NOT_IN,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression '== ?' is boolean true(1)
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function is($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, 1, $modelClass, $tableAlias, $sqlLogical);
    }

    /**
     * Set in query part where expression '== ?' is boolean false(0)
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function not($fieldName, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->eq($fieldName, 0, $modelClass, $tableAlias, $sqlLogical);
    }

    /**
     * Set in query part where expression 'like ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function like($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        return $this->where(
            $sqlLogical,
            $fieldName,
            Query::SQL_COMPARSION_KEYWORD_LIKE,
            $value,
            $modelClass,
            $tableAlias
        );
    }

    /**
     * Set in query part where expression 'rlike ?'
     *
     * @param $fieldName
     * @param $value
     * @param null $modelClass
     * @param null $tableAlias
     * @param string $sqlLogical
     * @return $this
     */
    public function rlike($fieldName, $value, $modelClass = null, $tableAlias = null, $sqlLogical = Query::SQL_LOGICAL_AND)
    {
        if (!$modelClass) {
            $modelClass = $this->getModelClass();
        }

        $modelFields = $modelClass::getMapping()->getFieldNames();
        $fieldValue = $modelClass::getFieldName($value);

        /** check ability use pattern from field in base */
        return array_key_exists($fieldValue, $modelFields)
            ? $this->where($sqlLogical, $fieldValue, Query::SQL_COMPARSION_KEYWORD_RLIKE_REVERSE, $fieldName, $modelClass, $tableAlias)
            : $this->where($sqlLogical, $modelClass::getFieldName($fieldName), Query::SQL_COMPARSION_KEYWORD_RLIKE, $value, $modelClass, $tableAlias);
    }

    /**
     * Set inner join query part
     *
     * @param $modelClass
     * @param $fieldNames
     * @param null $tableAlias
     * @param null $condition
     * @return $this
     */
    public function inner($modelClass, $fieldNames, $tableAlias = null, $condition = null)
    {
        return $this->select($fieldNames, null, $modelClass, $tableAlias)
            ->join(Query::SQL_CLAUSE_INNER_JOIN, $modelClass, $tableAlias, $condition);
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
        if ($this->_sql !== null) {
            throw new Exception('Запрос уже оттранслирован ранее. Внесение изменений в запрос не принесет никаких результатов');
        }

        if (!$tableAlias) {
            $tableAlias = $modelClass == $this->getModelClass()
                ? $this->getTableAlias()
                : Object::getName($modelClass);
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

            $joins = [['class' => $this->getModelClass(), 'alias' => Object::getName($this->getModelClass())]];

            if (!empty($this->_sqlParts[self::PART_JOIN])) {
                $joins = array_merge($joins, $this->_sqlParts[self::PART_JOIN]);
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
                throw new Exception('Could not defined condition for join part of sql query', $this->_sqlParts);
            }
        }

        $currentJoin['on'] = $condition;

        $this->_sqlParts[self::PART_JOIN][] = $currentJoin;

        return $this;
    }

    /**
     * Set data of query part select
     *
     *  $_sqlPart[self::PART_SELECT] = [
     *      $modelClass => [
     *          $tableAlias, [
     *             $fieldName => $fieldAlias,
     *             $fieldName2 => $fieldAlias2,
     *          ]
     *      ]
     *  ];
     *
     * @param $fieldName
     * @param null $fieldAlias
     * @param null $modelClass
     * @param null $tableAlias
     * @throws Exception
     * @return $this
     */
    public function select($fieldName, $fieldAlias = null, $modelClass = null, $tableAlias = null)
    {
        if ($this->_sql !== null) {
            throw new Exception('Запрос уже оттранслирован ранее. Внесение изменений в запрос не принесет никаких результатов');
        }

        if (empty($fieldName)) {
            return $this;
        }

        if (!$modelClass) {
            $modelClass = $this->getModelClass();
        }

        if (!$tableAlias) {
            $tableAlias = $modelClass == $this->getModelClass()
                ? $this->getTableAlias()
                : Object::getName($modelClass);
        }

        if ($fieldName == '*') {
            $fieldName = $modelClass::getFieldNames();
        }

        if (is_array($fieldName)) {
            foreach ($fieldName as $field => $fieldAlias) {
                if (is_numeric($field)) {
                    $this->select($fieldAlias, null, $modelClass, $tableAlias);
                } else {
                    $this->select($field, $fieldAlias, $modelClass, $tableAlias);
                }
            }

            return $this;
        } else {
            $fieldName = explode(',', $fieldName);

            if (count($fieldName) > 1) {
                $this->select($fieldName, null, $modelClass, $tableAlias);

                return $this;
            } else {
                $fieldName = reset($fieldName);
            }
        }

        $fieldName = $modelClass::getFieldName($fieldName);

        if (!$fieldAlias) {
            $fieldAlias = $fieldName;
        }

        if (!isset($this->_sqlParts[self::PART_SELECT][$modelClass])) {
            $pkName = $modelClass::getFieldName('/pk');

            $this->_sqlParts[self::PART_SELECT][$modelClass] = [
                $tableAlias, [
                    $pkName => $pkName
                ]
            ];
        }

        $this->_sqlParts[self::PART_SELECT][$modelClass][1][$fieldName] = $fieldAlias;

        $this->appendCacheTag($modelClass, $fieldName, true, false);

        return $this;
    }

    /**
     * Set inner join query part
     *
     * @param $modelClass
     * @param $fieldNames
     * @param null $tableAlias
     * @param null $condition
     * @return $this
     */
    public function left($modelClass, $fieldNames, $tableAlias = null, $condition = null)
    {
        return $this->select($fieldNames, null, $modelClass, $tableAlias)
            ->join(Query::SQL_CLAUSE_LEFT_JOIN, $modelClass, $tableAlias, $condition);
    }

    /**
     * Set data for values query part of insert
     *
     *  $values = [
     *      [
     *          'name' => 'Petya',
     *          'surname' => 'Ivanov'
     *      ],
     *      [
     *          'name' => 'Vasya',
     *          'surname' => 'Petrov'
     *      ],
     *  ];
     *
     * @param $key
     * @param null $value
     * @return Query
     */
    public function values($key, $value = null)
    {
        if ($value !== null) {
            return $this->values([[$key => $value]]);
        }

        if (!is_array(reset($key))) {
            return $this->values([$key]);
        }

        $modelClass = $this->getModelClass();
        $fieldNames = [];

        foreach (array_keys(reset($key)) as $fieldName) {
            $fieldNames[] = $modelClass::getFieldName($fieldName);
        }

        $this->_sqlParts[self::PART_VALUES] = [$modelClass, $fieldNames, count($key)];

        $this->appendCacheTag($modelClass, $fieldNames, false, true);

        foreach ($key as $value) {
            $this->_bindParts[self::PART_VALUES] = array_merge($this->_bindParts[self::PART_VALUES], array_values($value));
        }

        return $this;
    }

    /**
     * Set data for set query part of update
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function set($key, $value = null)
    {
        if ($value !== null) {
            return $this->set([[$key => $value]]);
        }

        if (!is_array(reset($key))) {
            return $this->set([$key]);
        }

        $modelClass = $this->getModelClass();
        $fieldNames = [];

        foreach (array_keys(reset($key)) as $fieldName) {
            $fieldNames[] = $modelClass::getFieldName($fieldName);
        }

        $this->_sqlParts[self::PART_SET] = [$modelClass, $fieldNames, count($key)];

        $this->appendCacheTag($modelClass, $fieldNames, false, true);

        foreach ($key as $value) {
            $this->_bindParts[self::PART_SET] = array_merge($this->_bindParts[self::PART_SET], array_values($value));
        }

        return $this;
    }

    /**
     * Set flag of get count rows
     *
     * @param $fieldName
     * @param null $fieldAlias
     * @param null $modelClass
     * @param null $tableAlias
     * @return $this
     */
    public function count($fieldName = '/pk', $fieldAlias = null, $modelClass = null, $tableAlias = null)
    {
        if (!$modelClass) {
            $modelClass = $this->getModelClass();
        }

        $fieldName = $modelClass::getFieldName($fieldName);

        if (!$fieldAlias) {
            $fieldAlias = $fieldName . '_count';
        }

        $this->appendCacheTag($modelClass, $fieldName, true, false);

        return $this->select('count(' . $fieldName . ')', $fieldAlias, $modelClass, $tableAlias);
    }

    public function calcFoundRows()
    {
        $this->_sqlParts[self::PART_SELECT]['_calcFoundRows'] = true;
        return $this;
    }

    /**
     * Ascending ordering
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @return $this
     */
    public function asc($fieldName, $modelClass = null, $tableAlias = null)
    {
        return $this->order($fieldName, 'ASC', $modelClass, $tableAlias);
    }

    /**
     * Ordering
     *
     * @param $fieldName
     * @param $isAscending
     * @param $modelClass
     * @param $tableAlias
     * @return $this
     */
    private function order($fieldName, $isAscending, $modelClass = null, $tableAlias = null)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $name) {
                $this->order($name, $isAscending, $modelClass, $tableAlias);
            }

            return $this;
        }

        if (!$modelClass) {
            $modelClass = $this->getModelClass();
        }

        if (!$tableAlias) {
            $tableAlias = $modelClass == $this->getModelClass()
                ? $this->getTableAlias()
                : Object::getName($modelClass);
        }

        $fieldName = $modelClass::getFieldName($fieldName);

        if (!isset($this->_sqlParts[self::PART_ORDER][$modelClass])) {
            $this->_sqlParts[self::PART_ORDER][$modelClass] = [
                $tableAlias, [
                    $fieldName => $isAscending
                ]
            ];
        } else {
            $this->_sqlParts[self::PART_ORDER][$modelClass][1][$fieldName] = $isAscending;
        }

        return $this;
    }

    /**
     * Descending ordering
     *
     * @param $fieldName
     * @param null $modelClass
     * @param null $tableAlias
     * @return $this
     */
    public function desc($fieldName, $modelClass = null, $tableAlias = null)
    {
        return $this->order($fieldName, 'DESC', $modelClass, $tableAlias);
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
        $this->_sqlParts[self::PART_LIMIT] = [$limit, $offset];
        return $this;
    }

    /**
     * Dump of query (sql and binds)
     *
     * @param $dataSourceName
     * @return array
     */
    public function getDump($dataSourceName = 'Mysqli')
    {
        return ['sql' => $this->getSql($dataSourceName), 'binds' => $this->getBinds()];
    }

    /**
     * Casts query to string
     *
     * @param $dataSourceName
     * @throws Exception
     * @return string
     */
    public function getSql($dataSourceName)
    {
        if ($this->_sql !== null) {
            return $this->_sql;
        }

        $queryTranslator = Query_Translator::getInstance('Ice:' . $dataSourceName);

        if ($this->getQueryType() != self::TYPE_SELECT) {
            return $queryTranslator->translate($this);
        }

        $queryCacheDataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__ . '/sql'));
        $sqlPartsHash = $this->getSqlPartsHash();

        $sql = $queryCacheDataProvider->get($sqlPartsHash);

        if (empty($sql)) {
            $sql = $queryTranslator->translate($this);
            $queryCacheDataProvider->set($sqlPartsHash, $sql, 86400);
        }

        $this->_sql = $sql;

        return $this->_sql;
    }

    public function getQueryType()
    {
        return $this->_queryType;
    }

    public function getSqlPartsHash()
    {
        return crc32(String::serialize($this->getSqlPart()));
    }

    /**
     * @param null $sqlPart
     *
     * @return array
     */
    public function getSqlPart($sqlPart = null)
    {
        if (!$sqlPart) {
            return $this->_sqlParts;
        }

        return isset($this->_sqlParts[$sqlPart]) ? $this->_sqlParts[$sqlPart] : null;
    }

    public function getBinds()
    {
        if ($this->_binds !== null) {
            return $this->_binds;
        }

        $this->_binds = [];

        foreach ($this->getBindPart() as $bind) {
            if (!is_array(reset($bind))) {
                $this->_binds = array_merge($this->_binds, $bind);
                continue;
            }

            foreach ($bind as $values) {
                $this->_binds = array_merge($this->_binds, $values);
                continue;
            }
        }

        return $this->_binds;
    }

    /**
     * @param null $bindPart
     * @return array
     */
    public function getBindPart($bindPart = null)
    {
        if (!$bindPart) {
            return $this->_bindParts;
        }

        return isset($this->_bindParts[$bindPart]) ? $this->_bindParts[$bindPart] : null;
    }

    public function getCacheTags()
    {
        return $this->_cacheTags;
    }

    public function getHash()
    {
        return $this->getSqlPartsHash() . '/' . $this->getBindPartsHash();
    }

    public function getBindPartsHash()
    {
        return crc32(String::serialize($this->getBindPart()));
    }

    public function getValidateTags()
    {
        return $this->getCacheTags()['validate'];
    }

    public function getInvalidateTags()
    {
        return $this->getCacheTags()['invalidate'];
    }

    public function getCache(Cacheable $cacheable)
    {
        $queryType = $cacheable->getQueryType();

        switch ($queryType) {
            case Query::TYPE_SELECT:
                $hash = $this->getHash();

                $cacheDataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__ . '/query'));
                $cache = $cacheDataProvider->get($hash);

                if (!$cache) {
                    $cache = ['tags' => $this->getValidateTags(), 'time' => 0];
                }

                if (Cache::validate(__CLASS__, $cache['tags'], $cache['time'])) {
                    return $cache;
                }

                $cache['data'] = $cacheable->getDataSource()->$queryType($this);
                $cache['time'] = time();

                $cacheDataProvider->set($hash, $cache);
                break;

            case Query::TYPE_INSERT:
            case Query::TYPE_UPDATE:
            case Query::TYPE_DELETE:
                $cache['data'] = $cacheable->getDataSource()->$queryType($this);
                Cache::invalidate(__CLASS__, $this->getInvalidateTags());
                break;

            default:
                throw new Exception('Unknown data source query statment type "' . $queryType . '"');
        }

        return $cache;
    }
}