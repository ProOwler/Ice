<?php
/**
 *
 * @desc Помощник для работы с json
 * @package Ice
 *
 */

namespace ice\helper;

use ice\Exception;

class Json
{
    /**
     * @param $json
     * @return array
     * @throws Exception
     */
    public static function decode($json)
    {
        $data = json_decode($json, true);

        $error = json_last_error();

        if (!$error) {
            return $data;
        }

        switch ($error) {
            case JSON_ERROR_DEPTH:
                throw new Exception('JSON - Достигнута максимальная глубина стека', print_r($json, true));
            case JSON_ERROR_STATE_MISMATCH:
                throw new Exception('JSON - Некорректные разряды или не совпадение режимов', print_r($json, true));
            case JSON_ERROR_CTRL_CHAR:
                throw new Exception('JSON - Некорректный управляющий символ', print_r($json, true));
            case JSON_ERROR_SYNTAX:
                throw new Exception('JSON - Синтаксическая ошибка, не корректный JSON', print_r(
                    $json, true));
            case JSON_ERROR_UTF8:
                throw new Exception('JSON - Некорректные символы UTF-8, возможно неверная кодировка', print_r($json, true));
            default:
                throw new Exception('JSON - Неизвестная ошибка', print_r($json, true));
        }
    }

    /**
     * @param $data
     * @param bool $isPretty
     * @return string
     */
    public static function encode($data, $isPretty = false)
    {
        return $isPretty
            ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            : json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
