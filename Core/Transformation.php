<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 21.03.14
 * Time: 15:53
 */

namespace ice\core;

/**
 * Transformation of data rows
 *
 * @package ice\core
 * @author dp
 */
abstract class Transformation
{

    /**
     * Return inctance of Transform class
     *
     * @param $transformationName
     * @return Transformation
     */
    public static function getInstance($transformationName)
    {
        $transformation = $transformationName;

        return new \Transformation_User_Avatar();
    }

    /**
     * Apply transformation for data rows
     *
     * @param $modelClass
     * @param array $rows
     * @param $params
     * @return array
     */
    abstract function transform($modelClass, array $rows, $params);
} 