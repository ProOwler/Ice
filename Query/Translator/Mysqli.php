<?php
namespace ice\query\translator;

use ice\core\Model;
use ice\core\Query;
use ice\core\Query_Translator;
use ice\Exception;
use ice\helper\Data_Mapping;

class Mysqli extends Query_Translator
{
    const SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';
    const SQL_STATEMENT_SELECT = 'SELECT';
    const SQL_STATEMENT_INSERT = 'INSERT';
    const SQL_STATEMENT_UPDATE = 'UPDATE';
    const SQL_STATEMENT_DELETE = 'DELETE';
    const SQL_CLAUSE_FROM = 'FROM';
    const SQL_CLAUSE_INTO = 'INTO';
    const SQL_CLAUSE_SET = 'SET';
    const SQL_CLAUSE_VALUES = 'VALUES';
    const SQL_CLAUSE_WHERE = 'WHERE';
    const SQL_CLAUSE_ORDER = 'ORDER';
    const SQL_CLAUSE_LIMIT = 'LIMIT';

    public function translate(Query $query)
    {
        $sql = '';

        foreach ($query->getSqlPart() as $sqlPart => $data) {
            if (empty($data)) {
                continue;
            }

            $translate = 'translate' . ucfirst($sqlPart);
            $sql .= $this->$translate($data);
        }

        if (empty(trim($sql))) {
            throw new Exception('Sql query is empty');
        }

        return $sql;
    }

    private function translateValues(array $data)
    {
        $sql = '';

        if (empty($data)) {
            return $sql;
        }

        list($modelClass, $fieldNames, $count) = $data;

        $sql .= "\n" . self::SQL_STATEMENT_INSERT . ' ' . self::SQL_CLAUSE_INTO .
            "\n\t" . Data_Mapping::getTableNameByClass($modelClass);
        $sql .= "\n\t" . '(`' . implode('`,`', $fieldNames) . '`)';
        $sql .= "\n" . self::SQL_CLAUSE_VALUES;

        $values = "\n\t" . '(?' . str_repeat(',?', count($fieldNames) - 1) . ')';

        $sql .= $values;

        if ($count > 1) {
            $sql .= str_repeat(',' . $values, $count - 1);
        }

        return $sql;
    }

    private function translateSet(array $data)
    {
        $sql = '';

        if (empty($data)) {
            return $sql;
        }

        list($modelClass, $fieldNames, $count) = $data;

        $sql .= "\n" . self::SQL_STATEMENT_UPDATE .
            "\n\t" . Data_Mapping::getTableNameByClass($modelClass);
        $sql .= "\n" . self::SQL_CLAUSE_SET;
        $sql .= "\n\t" . '`' . implode('` = ?, `', $fieldNames) . '` = ?';

        return $sql;
    }

    private function translateWhere(array $data)
    {
        $sql = '';
        $delete = '';

        $deleteClass = array_shift($data);

        if ($deleteClass) {
            $delete = "\n" . self::SQL_STATEMENT_DELETE . ' ' . self::SQL_CLAUSE_FROM .
            "\n\t" . Data_Mapping::getTableNameByClass($deleteClass);
            $sql .= $delete;
        }

        if (empty($data)) {
            return $sql;
        }

        $sql = '';

        foreach ($data as $modelClass => list($tableAlias, $fieldNames)) {
            $modelMapping = $modelClass::getMapping()->getFieldNames();

            foreach ($fieldNames as list($logicalOperator, $fieldName, $comparsionOperator, $count)) {
                $whereQuery = null;

                if (isset($modelMapping[$fieldName])) {
                    $fieldName = $modelMapping[$fieldName];
                }

                switch ($comparsionOperator) {
                    case Query::SQL_COMPARSION_OPERATOR_EQUAL:
                        $whereQuery = '`' . $fieldName . '` ' . Query::SQL_COMPARSION_OPERATOR_EQUAL . ' ?';
                        break;
                    case Query::SQL_COMPARSION_OPERATOR_GREATER:
                        $whereQuery = '`' . $fieldName . '` ' . Query::SQL_COMPARSION_OPERATOR_GREATER . ' ?';
                        break;
                    case Query::SQL_COMPARSION_OPERATOR_LESS:
                        $whereQuery = '`' . $fieldName . '` ' . Query::SQL_COMPARSION_OPERATOR_LESS . ' ?';
                        break;
                    case Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL:
                        $whereQuery = '`' . $fieldName . '` ' . Query::SQL_COMPARSION_OPERATOR_GREATER_OR_EQUAL . ' ?';
                        break;
                    case Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL:
                        $whereQuery = '`' . $fieldName . '` ' . Query::SQL_COMPARSION_OPERATOR_LESS_OR_EQUAL . ' ?';
                        break;
                    case Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL:
                        $whereQuery = $fieldName . ' ' . Query::SQL_COMPARSION_OPERATOR_NOT_EQUAL . ' ?';
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_IN:
                        $whereQuery = $fieldName . ' IN (?' . str_repeat(',?', $count - 1) . ')';
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_IS_NULL:
                        $whereQuery = $fieldName . ' ' . Query::SQL_COMPARSION_KEYWORD_IS_NULL;
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL:
                        $whereQuery = $fieldName . ' ' . Query::SQL_COMPARSION_KEYWORD_IS_NOT_NULL;
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_LIKE:
                        $whereQuery = $fieldName . ' ' . Query::SQL_COMPARSION_KEYWORD_LIKE . ' ?';
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_RLIKE:
                        $whereQuery = $fieldName . ' ' . Query::SQL_COMPARSION_KEYWORD_RLIKE . ' ?';
                        break;
                    case Query::SQL_COMPARSION_KEYWORD_RLIKE_REVERSE:
                        $whereQuery = '? ' . Query::SQL_COMPARSION_KEYWORD_RLIKE . ' ' . $fieldName;
                        break;
                    default:
                        throw new Exception('Unknown comparsion operator "' . $comparsionOperator . '"');
                }

                $sql .= $sql
                    ? ' ' . $logicalOperator . "\n\t"
                    : "\n" . self::SQL_CLAUSE_WHERE . "\n\t";
                $sql .= $whereQuery;
            }
        }

        return empty($delete) ? $sql : $delete . $sql;
    }

    private function translateSelect(array $data)
    {
        $sql = '';

        $calcFoundRows = array_shift($data);

        if (empty($data)) {
            return $sql;
        }

        $fields = [];

        foreach ($data as $modelClass => list($tableAlias, $fieldNames)) {
            array_walk($fieldNames, function (&$fieldAlias, $fieldName, $info) {
                    list($tableAlias, $modelMapping) = $info;

                    if (isset($modelMapping[$fieldName])) {
                        $fieldName = $modelMapping[$fieldName];

                        $fieldAlias = $fieldAlias == $fieldName
                            ? $fieldName
                            : $tableAlias . '.' . $fieldName . ' AS `' . $fieldAlias . '`';
                    } else {
                        $fieldAlias = $fieldName . ' AS `' . $fieldAlias . '`';
                    }
                },
                [$tableAlias, $modelClass::getMapping()->getFieldNames()]
            );

            $fields = array_merge($fields, $fieldNames);
        }

        if (empty($fields)) {
            return $sql;
        }

        reset($data);
        $from = each($data);

        $sql .= "\n" . self::SQL_STATEMENT_SELECT . ($calcFoundRows ? ' ' . self::SQL_CALC_FOUND_ROWS . ' ' : '') .
            "\n\t" . implode(',' . "\n\t", $fields) .
            "\n" . self::SQL_CLAUSE_FROM .
            "\n\t" . Data_Mapping::getTableNameByClass($from['key']) . ' `' . reset($from['value']) . '`';

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

    private function translateOrder(array $data)
    {
        $sql = '';

        if (empty($data)) {
            return $sql;
        }

        $orders = [];

        foreach ($data as $modelClass => list($tableAlias, $fieldNames)) {
            $modelMapping = $modelClass::getMapping()->getFieldNames();

            foreach ($fieldNames as $fieldName => $ascending) {
                $orders[] = $modelMapping[$fieldName] . ' ' . $ascending;
            }
        }

        $sql .= "\n" . 'ORDER BY ' .
            "\n\t" . implode(',' . "\n\t", $orders);

        return $sql;
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