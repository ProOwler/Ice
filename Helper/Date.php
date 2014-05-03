<?php
namespace ice\helper;


class Date
{
    /**
     * 2001-03-10 17:16:18 (формат MySQL DATETIME)
     *
     * @return string
     */
    public static function getCurrent()
    {
        return date("Y-m-d H:i:s");
    }
} 