<?php

namespace ice\helper;

/**
 * Helper Query
 *
 * @package ice\helper
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Query
{
    public static function convertWhereForFilter(\ice\core\Query $query)
    {
        $where = $query->getSqlPart('where');
        $binds = $query->getBindPart('where');

        $filterFields = [];

        array_shift($where);

        foreach ($where as list(, $fields)) {
            foreach ($fields as $field) {
                $values = [];
                for ($i = 0; $i < $field[3]; $i++) {
                    $value = array_shift($binds);
                    if ($value === null) {
                        $values = null;
                    } else {
                        $values[] = $value;
                    }
                }
                $field[3] = $values;
                $filterFields[] = $field;
            }
        }

        return $filterFields;
    }

    public static function getRowsByParts(\ice\core\Query $query)
    {

    }
}