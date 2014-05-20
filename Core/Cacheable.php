<?php
namespace ice\core;

/**
 * Interface Cacheable
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
interface Cacheable
{
    public function getHash();

    public function getValidateTags();

    public function getInvalidateTags();

    public function getCache(Cacheable $cacheable);
} 