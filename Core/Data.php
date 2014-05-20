<?php
namespace ice\core;

use ArrayAccess;
use Countable;
use ice\core\model\Collection;
use ice\Exception;
use ice\helper\Arrays;
use ice\helper\Serializer;
use Iterator;
use Serializable;

/**
 * Class of data
 *
 * @package ice\core
 * @author dp <denis.a.shestakov@gmail.com>
 */
class Data implements Iterator, ArrayAccess, Countable, Serializable
{
    const RESULT_MODEL_CLASS = 'modelName';
    const RESULT_ROWS = 'rows';
    const QUERY_DUMP = 'query_dump';
    const NUM_ROWS = 'numRows';
    const AFFECTED_ROWS = 'affectedRows';
    const INSERT_ID = 'insertId';
    const FOUND_ROWS = 'foundRows';
    const PAGE = 'page';
    const LIMIT = 'limit';
    /** @var array Default data */
    protected $_result = [
        self::RESULT_MODEL_CLASS => null,
        self::RESULT_ROWS => [],
        self::QUERY_DUMP => '',
        self::NUM_ROWS => 0,
        self::FOUND_ROWS => 0,
        self::AFFECTED_ROWS => 0,
        self::PAGE => 1,
        self::LIMIT => 1000,
        self::INSERT_ID => null
    ];
    private $isValid = false;
    /** @var int row index of iterator */
    private $position = 0;
    private $_transformations = [];

    /**
     * Constructor of data object
     *
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->_result = array_merge($this->_result, $result);
        $this->isValid = true;
    }

    /**
     * Get collection from data
     *
     * @return Collection
     */
    public function getCollection()
    {
        /** @var Model $modelClass */
        $modelClass = $this->getResult()[self::RESULT_MODEL_CLASS];

        $collection = $modelClass::getCollection();
        $collection->setData($this);

        return $collection;
    }

    /**
     * Result data
     *
     * @return array
     */
    protected function getResult()
    {
        if ($this->_transformations === null) {
            return $this->_result;
        }

        $this->_result[self::RESULT_ROWS] = $this->applyTransformations($this->_result[self::RESULT_ROWS]);

        return $this->_result;
    }

    private function applyTransformations($rows)
    {
        if (empty($this->_transformations)) {
            $this->_transformations = null;
            return $rows;
        }

        $transformData = [];
        foreach ($this->_transformations as list($transformationName, $params)) {
            $transformData[] = Transformation::getInstance($transformationName)
                ->transform($this->_result[self::RESULT_MODEL_CLASS], $rows, $params);
        }

        foreach ($rows as $key => &$row) {
            foreach ($transformData as $transform) {
                $row = array_merge($row, $transform[$key]);
            }
        }

        $this->_transformations = null;
        return $rows;
    }

    /**
     * Get value from data
     *
     * @desc Результат запроса - единственное значение.
     *
     * @param null $columnName
     * @return mixed
     */
    public function getValue($columnName = null)
    {
        $row = $this->getRow();
        return $row ? ($columnName ? $row[$columnName] : reset($row)) : null;
    }

    /**
     * Get first row from data
     *
     * @desc Результат запроса - единственная запись таблицы.
     *
     * @param null $pk
     * @return array|null
     */
    public function getRow($pk = null)
    {
        $rows = $this->getResult()[self::RESULT_ROWS];

        if (empty($rows)) {
            return null;
        }

        if (isset($pk)) {
            return isset($rows[$pk]) ? $rows[$pk] : null;
        }

        return reset($rows);
    }

    /**
     * Return all rows from data as array
     *
     * @return array
     */
    public function getRows()
    {
        $rows = $this->getResult()[self::RESULT_ROWS];
        return empty($rows) ? [] : $rows;
    }

    /**
     * Return model from data
     *
     * @return Model|null
     */
    public function getModel()
    {
        $row = $this->getRow();

        if (empty($row)) {
            return null;
        }

        /** @var Model $modelClass */
        $modelClass = $this->getResult()[self::RESULT_MODEL_CLASS];

        return $modelClass::create($row);
    }

    /**
     * Add row to data
     *
     * @param $pk
     * @param $fieldName
     * @param null $value
     * @internal param array $row
     */
    public function setRow($pk, $fieldName, $value = null)
    {
        $row = isset($this->_result[DATA::RESULT_ROWS][$pk])
            ? $this->_result[DATA::RESULT_ROWS][$pk] : [];

        if (is_array($fieldName)) {
            $row = array_merge($row, $fieldName);
        } else {
            $row[$fieldName] = $value;
        }

        $this->_result[DATA::RESULT_ROWS][$pk] = $row;
        $this->isValid = false;
    }

    /**
     * Return count of rows returned by query
     *
     * @return mixed
     */
    public function getNumRows()
    {
        return $this->_result[DATA::NUM_ROWS];
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
        return current($this->_result[DATA::RESULT_ROWS]);
    }

    /**
     * Move forward to next row of iterator
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->_result[DATA::RESULT_ROWS]);
        ++$this->position;
    }

    /**
     * Return index of iterator row
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @throws Exception
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Validation current row position of iterator
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return !empty(current($this->_result[DATA::RESULT_ROWS]));
    }

    /**
     * Reset iterator
     *
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (!empty($this->getResult()[DATA::RESULT_ROWS])) {
            reset($this->_result[DATA::RESULT_ROWS]);
        }

        $this->position = 0;
    }

    /**
     * Remove row from data by pk
     *
     * @param $pk
     * @return array
     */
    public function delete($pk)
    {
        $row = $this->_result[DATA::RESULT_ROWS][$pk];
        unset($this->_result[DATA::RESULT_ROWS][$pk]);
        return $row;
    }

    /**
     * Return sql of returned data
     *
     * @return string
     */
    public function getQueryDump()
    {
        return $this->_result[DATA::QUERY_DUMP];
    }

    public function addTransformation($transformation, $params)
    {
        if ($this->_transformations === null) {
            $this->_transformations = [];
        }

        $this->_transformations[] = [$transformation, $params];
        return $this;
    }

    public function filter($filterScheme)
    {
        $data = clone $this;
        $data->_result[DATA::RESULT_ROWS] = Arrays::filter($data->_result[DATA::RESULT_ROWS], $filterScheme);
        return $data;
    }

    public function getFoundRows()
    {
        return $this->_result[DATA::FOUND_ROWS];
    }

    public function getColumn($fieldName = null)
    {
        return $fieldName ? array_column($this, $fieldName) : $this->getKeys();
    }

    /**
     * Return keys of data
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->_result[DATA::RESULT_ROWS]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->_result[DATA::RESULT_ROWS][$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->_result[DATA::RESULT_ROWS][$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_result[DATA::RESULT_ROWS][] = $value;
        } else {
            $this->_result[DATA::RESULT_ROWS][$offset] = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_result[DATA::RESULT_ROWS][$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_result[DATA::RESULT_ROWS]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return Serializer::serialize($this->_result);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_result = Serializer::unserialize($serialized);
    }

    public function getInsertId()
    {
        return $this->_result[DATA::INSERT_ID];
    }
}