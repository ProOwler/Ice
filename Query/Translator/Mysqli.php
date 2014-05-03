<?php
namespace ice\query\translator;

use ice\helper\Data_Mapping;
use ice\core\Model;
use ice\core\Query;
use ice\core\query\Order;
use ice\core\Query_Translator;
use ice\Exception;

class Mysqli extends Query_Translator
{
    protected function select(Query &$query)
    {
        $sql = '';
        $binds = [];

        $sql .= $this->translateSelect(
            [$query->getModelClass(), $query->getTableAlias()],
            $query->getSelect(),
            $query->getSelectCount(),
            $query->isCalcFoundRows()
        );

        $sql .= $this->translateJoin($query->getJoin());

        list($whereSql, $whereBinds) = $this->translateWhere($query->getWhere(), $query->getModelClass());

        $sql .= $whereSql;
        $binds = array_merge($binds, $whereBinds);

        $sql .= $this->translateOrder($query->getOrder(), $query->getModelClass());
        $sql .= $this->translateLimit($query->getLimit());

        if (empty(trim($sql))) {
            throw new Exception('Query string could not by empty');
        }

        return [$sql, $binds];
    }

    private function translateSelect($from, array $select, $selectCount, $isCalcFoundRows)
    {
        if (empty($select) && !$selectCount) {
            return '';
        }

        list($fromClass, $tableAlias) = $from;

        $pkName = strtolower($fromClass::getModelName()) . '_pk';
        if (!array_key_exists($pkName , $select)) {
            $select[$pkName] = $pkName;
        }

        if ($selectCount) {
            $select = ['count(' . $selectCount . ')'];
        }

        array_walk(
            $select,
            function (&$fieldName, $alias, $modelMapping) {
                $fieldName = $fieldName == $alias
                    ? (isset($modelMapping[$fieldName])
                        ? $modelMapping[$fieldName] . ' AS `' . $fieldName . '`'
                        : $fieldName)
                    : $fieldName . ' AS `' . $alias . '`';
            },
            $fromClass::getMapping()->getFieldNames()
        );

        $sql = "\n" . Query::SQL_STATEMENT_SELECT . ($isCalcFoundRows ? ' ' . Query::SQL_CALC_FOUND_ROWS . ' ' : '') .
            "\n\t" . implode(',' . "\n\t", $select);
        $sql .= "\n" . Query::SQL_CLAUSE_FROM .
            "\n\t" . Data_Mapping::getTableNameByClass($fromClass) . ' `' . $tableAlias . '`';

        return $sql;
    }

    private function translateJoin(array $join)
    {
        $sql = '';

        if (empty($join)) {
            return $sql;
        }

        foreach ($join as $joinTable) {
            $sql .= "\n" . $joinTable['type'] . "\n\t" .
                Data_Mapping::getTableNameByClass($joinTable['class']) . ' AS `' . $joinTable['alias'] .
                '` ON (' . $joinTable['on'] . ')';
        }

        return $sql;
    }

    private function translateWhere(array $where, $modelClass)
    {
        $sql = '';
        $binds = [];

        if (empty($where)) {
            return [$sql, $binds];
        }

        $modelMappingFieldNames = $modelClass::getMapping()->getFieldNames();

        foreach ($where as list($part, $bind)) {
            $whereQuery = null;

            if (isset($modelMappingFieldNames[$part[Query::CLAUSE_WHERE_FIELD_NAME]])) {
                $part[Query::CLAUSE_WHERE_FIELD_NAME] = $modelMappingFieldNames[$part[Query::CLAUSE_WHERE_FIELD_NAME]];
            }

            switch ($part[Query::CLAUSE_WHERE_COMPARSION_OPERATOR]) {
                case Query::SQL_COMPARSION_OPERATOR_EQUAL:
                    $whereQuery = '`' . $part[Query::CLAUSE_WHERE_FIELD_NAME] . '` ' . Query::SQL_COMPARSION_OPERATOR_EQUAL . ' ?';
                    break;
                case Query::SQL_COMPARSION_OPERATOR_GREATER:
                    $whereQuery = '`' . $part[Query::CLAUSE_WHERE_FIELD_NAME] . '` ' . Query::SQL_COMPARSION_OPERATOR_GREATER . ' ?';
                    break;
                case Query::SQL_COMPARSION_OPERATOR_LESS:
                    $whereQuery = '`' . $part[Query::CLAUSE_WHERE_FIELD_NAME] . '` ' . Query::SQL_COMPARSION_OPERATOR_LESS . ' ?';
                    break;
                case Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL:
                    $whereQuery = '`' . $part[Query::CLAUSE_WHERE_FIELD_NAME] . '` ' . Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL . ' ?';
                    break;
                case Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL:
                    $whereQuery = '`' . $part[Query::CLAUSE_WHERE_FIELD_NAME] . '` ' . Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL . ' ?';
                    break;
                case Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' ' . Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL . ' ?';
                    break;
                case Query::SQL_COMPARSION_KEYWORD_IN:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' IN (?' . str_repeat(
                            ',?', count($bind) - 1) . ')';
                    break;
                case Query::SQL_COMPARSION_KEYWORD_IS_NULL:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' ' . Query::SQL_COMPARSION_KEYWORD_IS_NULL;
                    break;
                case Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' ' . Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL;
                    break;
                case Query::SQL_COMPARSION_KEYWORD_LIKE:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' ' . Query::SQL_COMPARSION_KEYWORD_LIKE . ' ?';
                    break;
                case Query::SQL_COMPARSION_KEYWORD_RLIKE:
                    $whereQuery = $part[Query::CLAUSE_WHERE_FIELD_NAME] . ' ' . Query::SQL_COMPARSION_KEYWORD_RLIKE . ' ?';
                    break;
                case Query::SQL_COMPARSION_KEYWORD_RLIKE_REVERSE:
                    $whereQuery = '? ' . Query::SQL_COMPARSION_KEYWORD_RLIKE . ' ' . $part[Query::CLAUSE_WHERE_FIELD_NAME];
                    break;
                default:
                    throw new Exception('Unknown comparsion operator "' . $part[Query::CLAUSE_WHERE_COMPARSION_OPERATOR] . '"');
            }

            $sql .= $sql ? ' ' . $part[Query::CLAUSE_WHERE_LOGICAL_OPERATOR] . "\n\t" : "\n" . Query::SQL_CLAUSE_WHERE . "\n\t";
            $sql .= $whereQuery;

            if (is_array($bind)) {
                foreach ($bind as $b) {
                    $binds[] = $b;
                }
            } else {
                $binds[] = $bind;
            }
        }

        return [$sql, $binds];
    }

    protected function insert(Query &$query)
    {
        $sql = '';
        $binds = [];

        list($valuesSql, $valuesBinds) = $this->translateValues($query->getModelClass(), $query->getValues());

        $sql .= $valuesSql;
        $binds = array_merge($binds, $valuesBinds);

        if (empty(trim($sql))) {
            throw new Exception('Запрос не должен быть пустым');
        }

        return [$sql, $binds];
    }

    private function translateValues($insertClass, array $values)
    {
        $sql = '';
        $binds = [];

        if (empty($values)) {
            return [$sql, $binds];
        }

        if (count($values) == 1) {
            $value = array_filter(
                reset($values),
                function ($val) {
                    return $val !== null;
                }
            );

            $sql .= "\n" . Query::SQL_STATEMENT_INSERT . ' ' . Query::SQL_CLAUSE_INTO .
                "\n\t" . Data_Mapping::getTableNameByClass($insertClass);
            $sql .= "\n\t" . '(`' . implode('`,`', array_keys($value)) . '`)';
            $sql .= "\n" . Query::SQL_CLAUSE_VALUES;
            $sql .= "\n\t" . '(?' . str_repeat(',?', count($value) - 1) . ')';

            $binds += $value;

            return [$sql, $binds];
        }

        throw new Exception('need testing multi insert in one query');

        $sql .= "\n" . Query::SQL_STATEMENT_INSERT . ' ' . Query::SQL_CLAUSE_INTO .
            "\n\t" . Data_Mapping::getTableNameByClass($insertClass);
        $sql .= "\n\t" . '(' . implode(',', array_keys(reset($values))) . ')';
        $sql .= "\n" . Query::SQL_CLAUSE_VALUES;
        $sql .= "\n\t" . implode(
                ',' . "\n\t",
                array_map(
                    function ($value) {
                        return '(?' . str_repeat(',?', count($value) - 1) . ')';
                    },
                    $values
                )
            );

        foreach ($values as $value) {
            $binds += $value;
        }

        return [$sql, $binds];
    }

    protected function update(Query &$query)
    {
        $sql = '';
        $binds = [];

        list($setSql, $setBinds) = $this->translateSet($query->getModelClass(), $query->getSet());

        $sql .= $setSql;
        $binds = array_merge($binds, $setBinds);

        list($whereSql, $whereBinds) = $this->translateWhere($query->getWhere(), $query->getModelClass());

        $sql .= $whereSql;
        $binds = array_merge($binds, $whereBinds);

        if (empty(trim($sql))) {
            throw new Exception('Запрос не должен быть пустым');
        }

        return [$sql, $binds];
    }

    private function translateSet($updateClass, array $set)
    {
        $sql = '';
        $binds = [];

        if (empty($set)) {
            return [$sql, $binds];
        }

        $sql .= "\n" . Query::SQL_STATEMENT_UPDATE .
            "\n\t" . Data_Mapping::getTableNameByClass($updateClass);
        $sql .= "\n" . 'SET';
        $sql .= "\n\t" . implode(
                ',' . "\n\t",
                array_map(
                    function ($value) {
                        return '' . $value . ' = ?';
                    },
                    array_keys($set)
                )
            );

        foreach ($set as $value) {
            $binds[] = $value;
        }

        return [$sql, $binds];
    }

    protected function delete(Query &$query)
    {
        $sql = '';
        $binds = [];

        $sql .= $this->translateDelete($query->getModelClass());

        list($whereSql, $whereBinds) = $this->translateWhere($query->getWhere());

        $sql .= $whereSql;
        $binds = array_merge($binds, $whereBinds);

        return [$sql, $binds];
    }

    private function translateDelete($deleteClass)
    {
        return "\n" . Query::SQL_STATEMENT_DELETE . ' ' . Query::SQL_CLAUSE_FROM .
        "\n\t" . Data_Mapping::getTableNameByClass($deleteClass);
    }

    private function translateOrder($order, $modelClass)
    {
        if (empty($order)) {
            return '';
        }

        $modelMappingFieldNames = $modelClass::getMapping()->getFieldNames();

        $orders = [];
        foreach ($order as $fieldName => $ascending) {
            $orders[] = $modelMappingFieldNames[$fieldName] . ' ' . $ascending;
        }

        return "\n" . 'ORDER BY ' .
        "\n\t" . implode(',' . "\n\t", $orders);
    }

    private function translateLimit($limit)
    {
        if (empty($limit)) {
            return '';
        }

        list($limit, $offset) = $limit;

        return "\n" . 'LIMIT ' .
        "\n\t" . $offset . ', ' . $limit;
    }
}