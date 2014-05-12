<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 03.05.14
 * Time: 15:17
 */

namespace ice\helper;

class Phone
{
    public static function parse($number, $isOnlySigits = false)
    {
        $number = '+ 7' . preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $number);

        if ($isOnlySigits) {
            $number = preg_replace('/\D/', '', $number);
        }

        return $number;
    }
} 