<?php
namespace ice\helper;

class Transliterator
{
    public static function transliterate($string)
    {
        $string = transliterator_transliterate(
            "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();",
            $string
        );
        $string = preg_replace('/[-\s]+/', '_', $string);
        return trim($string, '_');
    }
} 