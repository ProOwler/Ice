<?php

namespace ice\helper;

/**
 * Транслятор дзен-кода
 *
 * @package ice\helper
 * @author sergb
 */
class Emmet
{

    /**
     *
     * @param string $emmetAbbreviation
     * @param array $vars
     * @return string
     */
    public static function translate($emmetAbbreviation, $vars = array())
    {
        $reEmmetDelimiter = '[>+]';
        $reEmmetElementTrivial = '[^>+*^]+?';
        $reEmmetBucksSpec = '(?:[$]+@?-?\d*)';
        $reEmmetElementTag = '(?:(?:[0-9a-zA-Z_-]|' . $reEmmetBucksSpec . ')*?)';
        $reEmmetElementTagLeftBorder = '(?<=[^0-9a-zA-Z_-]|[$]|[$][@]|^)';
        $reEmmetElementAttributeSpec = '(?:[#.](?:[$0-9a-zA-Z_-]|' . $reEmmetBucksSpec . ')+|\[[^]]*\])';
        $reEmmetTextSpec = '([{](?:(?>[^{}]+)|(?-1))*[}])'; // Recursive RE! Limited to 15-levels.
        $reEmmetElementAttributesAndTextSpec = '(?:' . $reEmmetElementAttributeSpec . '*' . $reEmmetTextSpec . '?)';
        $reEmmetElementSpec = $reEmmetElementTag . '' . $reEmmetElementAttributesAndTextSpec . '(?:[*]' . $reEmmetElementTrivial . ')??';
        $reEmmetElementReplace = '/^(' . $reEmmetElementTag . ')(' . $reEmmetElementAttributesAndTextSpec . ')((?:[*]' . $reEmmetElementTrivial . ')??)$/';
        $reEmmetLastElementMatch = '/^(.*?)' . $reEmmetElementTagLeftBorder . '(' . $reEmmetElementSpec . ')(' . $reEmmetDelimiter . '?)$/';

        $emmetAbbreviationRemain = $emmetAbbreviation;
        $previosTranslatedElement = '';

        $callback = function ($matches) use (&$previosTranslatedElement, $vars) {
            $r = self::translateElement($matches, $previosTranslatedElement, $vars);
            return $r;
        };
        $lengthEmmetAbbrevation = strlen($emmetAbbreviationRemain) + 1;
        while (strlen($emmetAbbreviationRemain) && $lengthEmmetAbbrevation > strlen($emmetAbbreviationRemain)) {
            if (!preg_match($reEmmetLastElementMatch, $emmetAbbreviationRemain, $matches)) {
                break;
            }
            $lengthEmmetAbbrevation = strlen($emmetAbbreviationRemain);
            $emmetAbbreviationRemain = $matches[1];
            $levelDelimiter = $matches[4]; // level delimiter detected
            $emmetElement = $matches[2];

            switch ($levelDelimiter) {
                case '+':
                    $translatedElement = self::translate($emmetElement, $vars);
                    $previosTranslatedElement = $translatedElement . $previosTranslatedElement;
                    break;
                case '>':
                case '': // конец строки (начальная точка разбора)
                    $previosTranslatedElement = preg_replace_callback($reEmmetElementReplace, $callback, $emmetElement);
                    break;
            }
        }
        return $emmetAbbreviationRemain . '' . $previosTranslatedElement;
    }

    /**
     * Трянсляция аббревиатуры одного элемента с подстановкой значений счетчика и переменных.
     * @param array $matches
     * @param string $innerHtml
     * @param array $vars
     * @return string
     */
    static function translateElement($matches, $innerHtml = '', $vars = array())
    {
//        $indentString = ' ';
//        $eol = PHP_EOL;
        $reEmmetBucksSpec = '(?:[$]+@?-?\d*)';

        $html = $innerHtml;
        $attrString = '';
        $emmetText = substr($matches[3], 1, -1); // обрезаем с краёв фигурные скобки
        $emmetAttrs = $matches[2];
        $tagName = $matches[1];
        $multiplier = $matches[4];
        //var_dump(__METHOD__); print_r($matches); // mega debug
        if (!empty($emmetText)) {
            $translatedText = self::translateText($emmetText, $vars);
            $html = $translatedText . $html;
        }
        if (!empty($emmetAttrs)) {
            // attributes: id & classes
            if (preg_match('/[#]((?:[0-9a-zA-Z_-]|' . $reEmmetBucksSpec . ')+)/', $emmetAttrs, $m)) {
                // берем первый из указанных id
                $attrString .= ' id="' . $m[1] . '"';
            }
            if (preg_match_all('/[.]((?:[0-9a-zA-Z_-]|' . $reEmmetBucksSpec . ')+)/', $emmetAttrs, $m)) {
                // выбираем все указанные классы
                $attrString .= ' class="' . join(' ', $m[1]) . '"';
            }
            if (preg_match_all('/\[([^]]*)\]/', $emmetAttrs, $m)) {
                // прочие атрибуты перенесем без изменений
                $attrString .= ' ' . join(' ', $m[1]);
            }
        }
        if (!empty($tagName)) {
            $html = '<' . $tagName . $attrString . '>' . $html . '</' . $tagName . '>';
        }
        if (!empty($multiplier)) {
            if (preg_match('/^[*](\d+)/', $multiplier, $m)) {
                $limit = intval($m[1]);
                $tmpHtml = '';
                for ($i = 1; $i <= $limit; $i++) {
                    $tmpHtml .= self::translateBucks($html, $limit, $i);
                }
                $html = $tmpHtml;
            }
        } else {
            $html = self::translateBucks($html, 1, 1);
        }
        return $html;
    }

    /**
     * Подставляет в текст значения именованных переменных.
     *
     * @param string $emmetText
     * @param array $vars
     * @return string
     */
    static function translateText($emmetText, $vars = array())
    {
        $reEmmetTextVarReplace = '/[{][$](\w+)[}]/';
        $callback = function ($matches) use ($vars) {
            return isset($vars[$matches[1]])
                ? $vars[$matches[1]]
                : $matches[0];
        };
        $translatedText = preg_replace_callback($reEmmetTextVarReplace, $callback, $emmetText);
        return $translatedText;
    }

    /**
     * Подставляет в текст значения счетчика.
     *
     * @param string $emmet Text
     * @param int $multiplierValue Общее число циклов.
     * @param int $counterValue Номер итерации в цикле.
     * @return string
     */
    public static function translateBucks($emmet, $multiplierValue, $counterValue = 1)
    {
        $reEmmetBucksReplace = '/([$]+)(@?(-?)(\d*))/';
        $callback = function ($matches) use ($multiplierValue, $counterValue) {
            $buckValue = $counterValue;
            $offset = 1;
            // присутствует ли модификатор?
            if (strlen($matches[2])) {
                // присутствует ли в модификаторе смещение?
                $offset = (strlen($matches[4])) ? intval($matches[4]) : 1;
                // присутствует ли в модификаторе минус?
                if (strlen($matches[3])) {
                    $buckValue = $multiplierValue + $offset - $counterValue;
                } else {
                    $buckValue = $offset + $counterValue - 1;
                }
            }
            $bucks = sprintf('%0' . strlen($matches[1]) . 'd', $buckValue);
            return $bucks;
        };
        $translatedText = preg_replace_callback($reEmmetBucksReplace, $callback, $emmet);
        return $translatedText;
    }

}

//$emmet = 'html>head>tex+oppo';
//echo '!Emmet: ' . $emmet;
//echo PHP_EOL . PHP_EOL;
//echo Emmet::translate($emmet);
//echo PHP_EOL . PHP_EOL;
//
//$emmet = 'k@docs.s';
//echo '!Emmet: ' . $emmet;
//echo PHP_EOL . PHP_EOL;
//echo Emmet::translate($emmet);
//echo PHP_EOL . PHP_EOL;
//
//$emmet = 'div#USER$.action.name>div#parent*4+asd.asd';
//echo '!Emmet: ' . $emmet;
//echo PHP_EOL . PHP_EOL;
//echo Emmet::translate($emmet);
//echo PHP_EOL . PHP_EOL;
//
//$emmet = 'html>head>link+body>div#parent$$of2*2>span{блок №$$$@- ($$@0) какого-то текста: {$t}-{$T}}*3';
//echo '!Emmet: ' . $emmet;
//echo PHP_EOL . PHP_EOL;
////echo Emmet::translate($emmet, ['t'=>'TexT']);
//echo PHP_EOL . PHP_EOL;

//$emmet = 'NonEmmet4&Start>p[attr1="e2"]#idf[attr1="e4"].FFF>sss*3>ddd{/>>}*3garbage>yo.lk';
//echo '!Emmet: ' . $emmet;
//echo PHP_EOL . PHP_EOL;
//echo Emmet::translate($emmet);
//echo PHP_EOL . PHP_EOL;
//
//
//die;