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
    /**
     * Filter array by filter scheme
     *
     *  $filterScheme = [
     *      ['name', 'Petya', '='],
     *      ['age', 18, '>'],
     *      ['surname', 'Iv%', 'like']
     *  ];
     *
     * @param array $rows
     * @param $filterScheme
     * @return array
     */
    public static function filter(array $rows, $filterScheme)
    {
        $filterFunction = function ($filterScheme) {
            return function ($row) use ($filterScheme) {
                foreach ($filterScheme as list($field, $value, $comparsion)) {
                    $field = trim($field);
                    $value = trim($value);
                    switch ($comparsion) {
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
                return true;
            };
        };

        return array_filter($rows, $filterFunction((array)$filterScheme));
    }
} 