<?php

namespace ice\query\translator;

use ice\core\Query;
use ice\core\Query_Translator;
use ice\Exception;

class Factory extends Query_Translator {

    protected function select(Query &$query)
    {
        return[$query->getHashParts(), []];
    }

    protected function insert(Query &$query)
    {
        throw new Exception('TODO: Implement insert() method.');
    }

    protected function update(Query &$query)
    {
        throw new Exception('TODO: Implement update() method.');
    }

    protected function delete(Query &$query)
    {
        throw new Exception('TODO: Implement delete() method.');
    }
}