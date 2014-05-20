<?php
namespace ice\core\action;

/**
 * Interface Factory
 *
 * @package ice\core\action
 * @author dp <denis.a.shestakov@gmail.com>
 */
interface Factory
{
    /**
     * Get delegate by name
     *
     * @param $delegateName
     * @return mixed
     */
    public static function getDelegate($delegateName);
}