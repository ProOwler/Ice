<?php
namespace ice\core;

use ice\helper\Data_Mapping;
use ice\helper\Date;
use ice\helper\Json;
use ice\helper\Object;
use ice\core\model\Collection;
use ice\core\model\Defined;
use ice\core\model\Factory;
use ice\Exception;

/**
 * Abstract core Class Model
 *
 * @package ice\core
 * @author dp
 */
abstract class Model
{
    /** @var array model fields */
    private $_row = [];
    /** @var array extended fields json */
    private $_json = [];
    /** @var array extended fields by primary key */
    private $_fk = [];
    /** @var array extended data of model */
    private $_data = [];
    /** @var array updated fields */
    private $_updates = [];

    /**
     * Private constructor. Create model: Model::create()
     *
     * @param array $row
     */
    private function __construct(array $row)
    {
        /** @var Model $modelClass */
        $modelClass = self::getClass();

        $modelSchemeColumns = $modelClass::getScheme()->getColumns();
        $modelMappingFields = $modelClass::getMapping()->getFieldNames();

        foreach ($modelMappingFields as $fieldName => $columnName) {

            $this->_row[$fieldName] = null;

            if (array_key_exists($fieldName, $row)) {
                $this->set($fieldName, $row[$fieldName], false);
                unset($row[$fieldName]);
                continue;
            }

            foreach (array('__json', '__fk', '_geo') as $ext) {
                $field = strstr($fieldName, $ext, true);
                if ($field !== false && array_key_exists($field, $row)) {
                    $this->set($field, $row[$field], false);
                    unset($row[$field]);
                    continue 2;
                }
            }

            $default = $modelSchemeColumns[$columnName]['default'];

            if ($default) {
                if ($default == 'CURRENT_TIMESTAMP') {
                    $default = Date::getCurrent();
                }

                $this->set($fieldName, $default);
            }
        }

        $this->_data = $row;
    }

    /**
     * Get class of model
     *
     * @param Model $modelClass
     *  class of short class (for example: Ice:User -> /ice/model/ice/User)
     *
     * @return Model
     */
    public static function getClass($modelClass = null)
    {
        if (!$modelClass) {
            $modelClass = get_called_class();
        }

        if (in_array('ice\core\model\Factory_Delegate', class_implements($modelClass))) {
            $modelClass = get_parent_class($modelClass);

            return $modelClass::getClass();
        }

        return $modelClass;
    }

    /**
     * Return scheme of table in data source: 'columnNames => (types, defaults, comments)')
     *
     * @return Model_Scheme
     */
    public static function getScheme()
    {
        return Model_Scheme::getInstance(self::getClass());
    }

    /**
     * Return mapping of model field names and column names in table of data source
     *
     * @example
     *  'model_id' => 'id'
     *  or
     * 'city__fk' => 'city_id'
     *
     * @return Model_Mapping
     */
    public static function getMapping()
    {
        return Model_Mapping::getInstance(self::getClass());
    }

    /**
     * Method set value of model field
     *
     * @code
     *  $user->set('/name', 'Guest'); // set value 'Guest' for field 'user_name'
     *  $user->set(['user_name' => 'Name', '/surname' => 'Surname']); // sets array params
     *  $user->set('data', ['data1' => 'string1', 'data2' => 'string2']); // set value of field data__json
     * @endcode
     *
     * @param $fieldName
     * @param null $fieldValue
     * @param bool $isUpdate
     * @return array
     * @throws Exception
     */
    public function set($fieldName, $fieldValue = null, $isUpdate = true)
    {
        if (is_array($fieldName)) {
            $set = [];

            foreach ($fieldName as $key => $value) {
                array_merge($set, $this->set($key, $value, $isUpdate));
            }

            return $set;
        }

        $fieldName = $this->getFieldName($fieldName);

        if (array_key_exists($fieldName, $this->_row)) {
//            var_dump($fieldName, $isUpdate, $this->_row[$fieldName] != $fieldValue, $this->_row[$fieldName], $fieldValue, "\n\n");
            if ($isUpdate && $this->_row[$fieldName] != $fieldValue) {
                $this->_updates[$fieldName] = $fieldValue;
            }

            $this->_row[$fieldName] = $fieldValue;

            return [[$fieldName => $this->_row[$fieldName]]];
        }

        $jsonFieldName = $fieldName . '__json';
        if (array_key_exists($jsonFieldName, $this->_row)) {
            if ($fieldValue == null) {
                $this->_json[$fieldName] = [];
                return $this->set($jsonFieldName, Json::encode($this->_json[$fieldName]));
            }

            $this->_json[$fieldName] = $fieldValue;

            return $this->set(
                $jsonFieldName,
                Json::encode(array_merge($this->get($fieldName), $this->_json[$fieldName])),
                $isUpdate
            );
        }

        $fkFieldName = $fieldName . '__fk';
        if (array_key_exists($fkFieldName, $this->_row)) {
            if ($fieldValue == null) {
                $this->_fk[$fieldName] = null;
                return $this->set($fkFieldName, null);
            }

            $this->_fk[$fieldName] = $fieldValue;
            return $this->set($fkFieldName, $fieldValue->getPk());
        }

        throw new Exception('Could not set value:' . "\n" .
            print_r($fieldValue, true) .
            'Field "' . $fieldName . '" not found in Model "' . $this->getModelName() . '"');
    }

    /**
     * Gets full model field name if send short name (for example: '/name' for model User -> user_name)
     *
     * @param $fieldName
     * @return string
     */
    public static function getFieldName($fieldName)
    {
        $isShort = strpos($fieldName, '/');

        if ($isShort === false) {
            return $fieldName;
        }

        $modelClass = self::getClass();

        $modelSchemeName = $isShort
            ? substr($fieldName, 0, $isShort)
            : $modelClass::getModelName();

        return strtolower($modelSchemeName) . '_' . substr($fieldName, $isShort + 1);
    }

    /**
     * Return simple name of model class
     *
     * @return string
     */
    public static function getModelName()
    {
        return Object::getName(self::getClass());
    }

    /**
     * Get value of model field
     *
     * @param null $fieldName
     * @return array
     * @throws Exception
     */
    public function get($fieldName = null, $isNotNull = true)
    {
        if ($fieldName === null) {
            return $this->_row;
        }

        $fieldName = $this->getFieldName($fieldName);

        foreach (array($this->_row, $this->_json, $this->_fk) as $fields) {
            if (array_key_exists($fieldName, $fields)) {
                if ($isNotNull && $fields[$fieldName] === null) {
                    throw new Exception('field "' . $fieldName . '" of model "' . $this->getModelName() . '" is null');
                }
                return $fields[$fieldName];
            }
        }

        $jsonFieldName = $fieldName . '__json';
        if (array_key_exists($jsonFieldName, $this->_row)) {
            $json = Json::decode($this->_row[$jsonFieldName]);

            if (empty($json)) {
                return [];
            }

            $this->_json[$fieldName] = $json;
            return $this->_json[$fieldName];
        }

        $foreignKeyName = strtolower(Object::getName(Model::getClass($fieldName))) . '__fk';
        if (array_key_exists($foreignKeyName, $this->_row)) {
            $fieldName = Model::getClass($fieldName);
            $key = $this->_row[$foreignKeyName];

            if (!$key) {
                throw new Exception('Model::__get: Не определен внешний ключ ' . $foreignKeyName . ' в модели ' . $this->getModelName());
            }

            $row = array_merge($this->_data, [strtolower(Object::getName($fieldName)) . '_pk' => $key]);
            $joinModel = $fieldName::create($row);

            if (!$joinModel) {
                throw new Exception('Model::__get: Не удалось получить модель по внешнему ключу ' .
                    $foreignKeyName . '="' . $key . '" в модели ' . $this->getModelName());
            }

            $this->_fk[$fieldName] = $joinModel;
            return $this->_fk[$fieldName];
        }

        throw new Exception('Field "' . $fieldName . '" not found in Model "' . $this->getModelName() . '"');
    }

    /**
     * Create model instance
     *
     * @param array $row
     * @return Model
     */
    public static function create(array $row)
    {
        /** @var Model $modelClass */
        $modelClass = get_called_class();

        if (isset(class_parents($modelClass)[Factory::getClass()])) {
            $modelClass = $modelClass . '_' . $row[$modelClass::getFieldName('/delegate_name')];
        }

        return new $modelClass($row);
    }

    /**
     * Get dataSource for current model class
     *
     * @return Data_Source
     */
    public static function getDataSource()
    {
        $modelName = self::getClass();
        $parentModelName = get_parent_class($modelName);

        if (
            $parentModelName == Defined::getClass() ||
            $parentModelName == Factory::getClass()
        ) {
            return Data_Source::getInstance(Object::getName($parentModelName) . ':model/' . $modelName);
        }

        return Data_Source::getDefault();
    }

    /**
     * Получение модели по первичному ключу
     *
     * @param $pk
     * @param array $fieldNames
     * @param Data_Source $dataSource
     * @return Model|null
     */
    public static function getModel($pk, $fieldNames = [], Data_Source $dataSource = null)
    {
        $modelClass = self::getClass();

        $model = Model_Repository::get($modelClass, $pk);

        if ($model) {
            return $model;
        }

        $model = Model::byQuery($modelClass::getQueryBuilder()->select($fieldNames)->pk($pk), $dataSource);

        if ($model) {
            Model_Repository::set($modelClass, $pk, $model);
        }

        return $model;
    }

    /**
     * Получение имен полей модели
     *
     * @param array $fields
     * @throws Exception
     * @return array
     */
    public static function getFieldNames($fields = [])
    {
        $modelClass = self::getClass();

        $fieldNames = array_keys($modelClass::getMapping()->getFieldNames());

        if (empty($fields)) {
            return $fieldNames;
        }

        foreach ((array)$fields as &$fieldName) {
            $fieldName = $modelClass::getFieldName($fieldName);

            if (in_array($fieldName, $fieldNames)) {
                continue;
            }

            if (in_array($fieldName . '__json', $fieldNames)) {
                $fieldName = $fieldName . '__json';
                continue;
            }

            throw new Exception('Поле "' . $fieldName . '" не найдено в модели "' . self::getClass() . '"');
        }

        return $fieldNames;
    }

    /**
     * Get model by query
     *
     * @param Query $query
     * @param array $fieldNames
     * @param Data_Source $dataSource
     * @return Model|null
     */
    public static function byQuery(Query $query, $fieldNames = [], Data_Source $dataSource = null)
    {
        return Collection::byQuery($query->limit(1), $fieldNames, $dataSource)->first();

    }

    /**
     * Return instance of query for current model class
     *
     * @param string $statementType
     * @param null $tableAlias
     * @return Query
     */
    public static function getQueryBuilder($statementType = 'select', $tableAlias = null)
    {
        return Query::getInstance($statementType, self::getClass(), $tableAlias);
    }

    /**
     * Get value from data of model
     *
     * @param null $key
     * @return array
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->_data;
        }

        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Set data in model data
     *
     * @param $key
     * @param null $value
     */
    public function setData($key, $value = null)
    {
        if (is_array($key) && !$value) {
            $this->_data = array_merge($this->_data, $key);
            return;
        }

        $this->_data[$key] = $value;
    }

    /**
     * Get primary key of model
     *
     * @return mixed
     */
    public function getPk()
    {
        /** @var Model $modelClass */
        $modelClass = self::getClass();
        return $this->_row[$modelClass::getPkName()];
    }

    /**
     * Get field name of primary key
     *
     * @return string
     */
    public static function getPkName()
    {
        /** @var Model $modelName */
        $modelName = self::getClass();
        return strtolower($modelName::getModelName()) . '_pk';
    }

    /**
     * Execute insert into data source
     *
     * @param Data_Source $dataSource
     * @return Model|null
     */
    public function insert(Data_Source $dataSource = null)
    {
        /** @var Model $modelClass */
        $modelClass = self::getClass();
        return $modelClass::getCollection()->add($this)->insert($dataSource)->first();
    }

    /**
     * Return collection of current model class name
     *
     * @param array $fieldNames
     * @return Collection
     */
    public static function getCollection(array $fieldNames = [])
    {
        return Collection::create(self::getClass(), $fieldNames);
    }

    /**
     * Execute update for model
     *
     * @param $key
     * @param null $value
     * @param Data_Source $dataSource
     * @return Model|null
     */
    public function update($key, $value = null, Data_Source $dataSource = null)
    {
        $this->set($key, $value);

        /** @var Model $modelClass */
        $modelClass = self::getClass();
        return $modelClass::getCollection()->add($this)->update($this->getUpdates(), $dataSource)->first();
    }

    /**
     * Return updated fields
     *
     * @return array
     */
    public function getUpdates()
    {
        return $this->_updates;
    }

    /**
     * Return array of extended fields by foreigen keys
     *
     * @return array
     */
    public function getFk()
    {
        return $this->_fk;
    }

    /**
     * Return array of extended fields by json fields
     *
     * @return array
     */
    public function getJson()
    {
        return $this->_json;
    }

    /**
     * Get array of fields names end their values
     *
     * @return array
     */
    public function getRow()
    {
        return $this->_row;
    }

    /**
     * Casts model to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)self::getClass();
    }
}