<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 20.03.14
 * Time: 20:33
 */

namespace ice\core\model\collection;

use ice\core\Data;
use ice\core\Model;

class Iterator extends Data
{

    function __construct(Data $data)
    {
        $this->setResult($data->getResult());
    }

    /**
     * @param array $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }

    /**
     * Return the current row of iterator
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        /** @var Model $modelClass */
        $modelClass = $this->_result[DATA::RESULT_MODEL_CLASS];
        return $modelClass::create(parent::current());
    }
}