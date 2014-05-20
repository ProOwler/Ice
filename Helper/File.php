<?php
namespace ice\helper;

/**
 * Helper for file working
 *
 * @package ice\helper
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class File
{
    public static function createData($fileName, $data)
    {
        Dir::get(dirname($fileName));
        return file_put_contents($fileName, '<?php' . "\n" . 'return ' . var_export($data, true) . ';');
    }
} 