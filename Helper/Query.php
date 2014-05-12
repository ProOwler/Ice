<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 07.05.14
 * Time: 15:15
 */

namespace ice\helper;

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
} 