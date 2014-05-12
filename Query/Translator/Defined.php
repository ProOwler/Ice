<?php
namespace ice\query\translator;

use ice\core\Query;
use ice\core\Query_Translator;
use ice\Exception;

class Defined extends Query_Translator
{

    public function translate(Query $query)
    {
        return $query->getSql('Mysqli');
    }
}