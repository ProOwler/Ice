<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 21.03.14
 * Time: 17:58
 */

namespace ice\helper;


use ice\Exception;

class Arrays
{
    public static function filter(array $rows, $filterScheme)
    {
        $filterFunction = function ($filterScheme) {
            return function ($row) use ($filterScheme) {
                $expr = ['<=', '>=', '<>', '=', '<', '>'];
                foreach ($filterScheme as $filter) {
                    foreach ($expr as $e) {
                        if (strpos($filter, $e)) {
                            list($field, $value) = explode($e, $filter);
                            $field = trim($field);
                            $value = trim($value);
                            switch ($e) {
                                case '<=':
                                    if ($row[$field] > $value) {
                                        return false;
                                    }
                                    break;
                                case '>=':
                                    if ($row[$field] < $value) {
                                        return false;
                                    }
                                    break;
                                case '<>':
                                    if ($row[$field] == $value) {
                                        return false;
                                    }
                                    break;
                                case '=':
                                    if ($row[$field] != $value) {
                                        return false;
                                    }
                                    break;
                                case '<':
                                    if ($row[$field] >= $value) {
                                        return false;
                                    }
                                    break;
                                case '>':
                                    if ($row[$field] <= $value) {
                                        return false;
                                    }
                                    break;
                                default:
                                    throw new Exception('Unknown comparsion operator');
                            };
                        }
                    }
                }
                return true;
            };
        };

        return array_filter($rows, $filterFunction((array) $filterScheme));
    }
} 