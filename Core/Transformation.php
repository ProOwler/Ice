<?php
namespace ice\core;

/**
 * Transformation of data rows
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
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