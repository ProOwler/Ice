<?php
namespace ice\helper;

class Transliterator
{
    public static function transliterate($string)
    {
        $string = function_exists('transliterator_transliterate')
            ? transliterator_transliterate(
                "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string
            )
            : self::translit($string);

        $string = preg_replace('/[-\s]+/', '_', $string);
        return trim($string, '_');
    }

    /**
     * @desc Перевод строки в транслит
     * @param string $value Исходна стока
     * @param bool|string $fromRussianToTranslit [optional] Направление перевода
     *         Если "en" - из русского на транслит,
     *         если "ru" - из транслита на русский
     * @return mixed|string Результат транслитации.
     */
    private static function translit($value, $fromRussianToTranslit = true)
    {
        $value = trim($value);

        $value = self::replaceSpecialChars($value);
        if ($fromRussianToTranslit === null) {
            $regexpRus = '/^[а-яА-Я]+/';
            $fromRussianToTranslit = preg_match($regexpRus, $value);
        }

        if ($fromRussianToTranslit) {
            // Сначала заменяем "односимвольные" фонемы.
            $value = self::u_strtr($value, "абвгдеёзийклмнопрстуфхыэ ", "abvgdeeziyklmnoprstufhie_");
            $value = self::u_strtr($value, "АБВГДЕЁЗИЙКЛМНОПРСТУФХЫЭ ", "ABVGDEEZIYKLMNOPRSTUFHIE_");

            // Затем - "многосимвольные".
            $value = self::u_strtr(
                $value,
                array(
                    "ж" => "zh", "ц" => "ts", "ч" => "ch", "ш" => "sh",
                    "щ" => "shch", "ь" => "", "ъ" => "", "ю" => "yu", "я" => "ya",
                    "Ж" => "ZH", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH",
                    "Щ" => "SHCH", "Ь" => "", "Ъ" => "", "Ю" => "YU", "Я" => "YA",
                    "ї" => "i", "Ї" => "Yi", "є" => "ie", "Є" => "Ye",
                    "&nbsp;" => "_"
                )
            );
        } else {
            // Сначала заменяем "многосимвольные".
            $value = self::u_strtr(
                $value,
                array(
                    "zh" => "ж", "ts" => "ц", "ch" => "ч", "sh" => "ш",
                    "shch" => "щ", "yu" => "ю", "ya" => "я",
                    "ZH" => "Ж", "TS" => "Ц", "CH" => "Ч", "SH" => "Ш",
                    "SHCH" => "Щ", "YU" => "Ю", "YA" => "Я",
                    "&nbsp;" => "_"
                )
            );

            //  Затем - "односимвольные" фонемы.
            $value = self::u_strtr($value, "abvgdeziyklmnoprstufh_", "абвгдезийклмнопрстуфх ");
            $value = self::u_strtr($value, "ABVGDEZIYKLMNOPRSTUFH_", "АБВГДЕЗИЙКЛМНОПРСТУФХ ");
        }

        return strtolower($value);
    }

    /**
     * @desc Удаление из строки спец. символы кроме '+'
     * @param $string
     * @param string $value Исходна стока
     * @return mixed Результат очистки.
     */
    private static function replaceSpecialChars($string, $value = ' ')
    {
        $value = str_replace(
            array(
                "\r", "\n", "\t", ',', '(', ')',
                '[', ']', '{', '}', '-', '_',
                '!', '@', '#', '$', '%', '^', ':',
                '&', '*', ',', '.', '=',
                '/', ' \\', '|', '\'', '"', '~', ' '
            ),
            $value,
            $string
        );
        return $value;
    }

    /**
     * @desc Заменяет символы в строке согласно переданным наборам.
     * @param string $value Исходая строка.
     * @param string|array $to Символы, которые будут вставлены на места
     * заменяемых.
     * @param string $from [optional] Символы, которые будут заменены.
     * Если этот аргумент не передан, в $to ожидается ассоциативный
     * массив вида "заменяемый символ" => "символ для замены".
     * @return string Результат замены
     */
    private static function u_strtr($value, $to, $from = null)
    {
        if (is_null($from)) {
            arsort($to, SORT_LOCALE_STRING);
            foreach ($to as $c => $r) {
                $value = str_replace($c, $r, $value);
            }
        } else {
            $len = min(strlen($to), strlen($from));
            for ($i = 0; $i < $len; ++$i) {
                $value = str_replace(
                    mb_substr($to, $i, 1, 'UTF-8'),
                    mb_substr($from, $i, 1, 'UTF-8'),
                    $value
                );
            }
        }
        return $value;
    }
} 