<?php

namespace ice\helper;

/**
 * Helper memory usage
 *
 * @package ice\helper
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Memory
{
    public static function getVarSize($var)
    {
        $start_memory = memory_get_usage();
        $tmp = unserialize(serialize($var));
        return memory_get_usage() - $start_memory;
    }

    public static function memoryGetUsagePeak()
    {
        $unit=array('B','KB','MB','GB','TB','PB');

        $size = memory_get_usage(true);
        $memoryUsage = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];

        $size = memory_get_peak_usage(true);
        $peakUsage = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        return 'memory_usage: ' . $memoryUsage . ' (peak: ' . $peakUsage . ')';
    }
} 