<?php
namespace ice\core;

use ice\helper\Object;
use ice\Ice;

/**
 * Abstract validator class
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
abstract class Validator
{
    public static function validateByScheme(array $data, array $validateScheme)
    {
        $errors = [];

        foreach ($validateScheme as $param => $scheme) {
            if (!isset($data[$param])) {
                $errors[$param] = 'Validator: param "' . $param . '" is not defined';
                continue;
            }

            $validator = is_array($scheme)
                ? Validator::getInstance($scheme['validator'])
                : Validator::getInstance($scheme);

            if ($validator->validate($data[$param], $scheme)) {
                continue;
            }

            $message = is_array($scheme) && isset($scheme['message'])
                ? $scheme['message']
                : 'Validator: param "' . $param . '" is not valid: ' . print_r($data[$param], true);

            $errors[$param] = $message;
        }

        return $errors;
    }

    public static function getInstance($name = null)
    {
        /** @var Validator $class */
        $class = $name
            ? Object::getClassByClassShortName(__CLASS__, $name)
            : get_called_class();

        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $object = $dataProvider->get($class);

        if ($object) {
            return $object;
        }

        $object = new $class();

        $dataProvider->set($class, $object);

        return $object;
    }

    /**
     * Validate data by scheme
     *
     * @example:
     *  'user_name' => [
     *      [
     *          'validator' => 'Ice:Not_Empty',
     *          'message' => 'Введите имя пользователя.'
     *      ],
     *  ],
     *  'name' => 'Ice:Not_Null'
     *
     * @param $data
     * @param null $scheme
     * @return boolean
     */
    public abstract function validate($data, $scheme = null);
}