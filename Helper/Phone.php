<?php
namespace ice\helper;

/**
 * Helper phone
 *
 * @package ice\helper
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
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