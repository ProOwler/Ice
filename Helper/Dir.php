<?php
namespace ice\helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Dir
{
    public static function get($path)
    {
        if (file_exists($path) && is_dir($path)) {
            return rtrim($path, '/') . '/';
        }

        $oldumask = umask(0);
        mkdir($path, 0775, true);
        umask($oldumask);

        return rtrim($path, '/') . '/';
    }

    public static function copy($source, $dest)
    {
        foreach ($sourceDirectoryIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            if ($item->isDir()) {
                $dirName = $dest . $sourceDirectoryIterator->getSubPathName();
                if (!file_exists($dirName)) {
                    mkdir($dirName);
                }
            } else {
                copy($item, $dest . $sourceDirectoryIterator->getSubPathName());
            }
        }
    }
} 