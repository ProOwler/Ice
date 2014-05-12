<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 12.05.14
 * Time: 7:42
 */

namespace ice\core;


interface Cacheable
{
    public function getHash();

    public function getValidateTags();

    public function getInvalidateTags();

    public function getCache(Cacheable $cacheable);
} 