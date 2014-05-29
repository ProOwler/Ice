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
    public static function createData($fileName, $data, $phpData = true, $flag = 0)
    {
        $owner = fileowner(Dir::get(dirname($fileName)));
        $data = $phpData ? '<?php' . "\n" . 'return ' . var_export($data, true) . ';' : $data;
        file_put_contents($fileName, $data, $flag);
        if (substr(sprintf('%o', fileperms($fileName)), -4) != 0664) {
//            chown($fileName, $owner);
            chmod($fileName, 0664);
        }
    }
} 