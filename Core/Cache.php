<?php

namespace ice\core;

use ice\Ice;

/**
 * Class Cache
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 * @since -0
 */
class Cache
{
    public static function validate($class, array $cacheTags, $time)
    {
        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $isValid = true;

        foreach (self::getKeys($cacheTags) as $key) {
            $tagCreate = $dataProvider->get($class . '/' . $key);

            if (!$tagCreate) {
                $tagCreate = time();
                $dataProvider->set($class . '/' . $key, $tagCreate);
            }

            if ($isValid) {
                $isValid = $tagCreate < $time;
            }
        }

        return $isValid;
    }

    private static function getKeys($cacheTags)
    {
        $keys = [];

        foreach ($cacheTags as $tagKey => $tagValue) {
            if (is_array($tagValue)) {
                $newKeys = self::getKeys($tagValue);

                foreach ($newKeys as &$tag) {
                    $tag = $tagKey . '/' . $tag;
                }

                $keys = array_merge($keys, $newKeys);
            } else {
                $keys[] = $tagKey;
            }
        }

        return $keys;
    }

    public static function invalidate($class, $cacheTags)
    {
        /** @var Data_Provider $dataProvider */
        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        foreach (self::getKeys($cacheTags) as $key) {
            $dataProvider->set($class . '/' . $key, time());
        }
    }
} 