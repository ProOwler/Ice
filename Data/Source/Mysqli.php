<?php
namespace ice\data\source;

use ice\core\Data;
use ice\core\Data_Source;
use ice\core\Model;
use ice\core\Query;
use ice\Exception;
use mysqli_stmt;

class Mysqli extends Data_Source
{
    /**
     * @param Query $query
     * @throws Exception
     * @return array
     */
    public function select(Query $query)
    {
        $statement = $this->getStatement($query);

        if ($statement->execute() === false) {
            $e = new Exception(
                'sql execute select error #' . $statement->errno . ': ' . $statement->error,
                $query->getDump($this->getName())
            );
            $statement->close();
            throw $e;
        }
//            $statement->store_result(); // Так почемуто не работает
        $result = $statement->get_result();

        if ($result === false) {
            $e = new Exception (
                'sql get result select error #' . $statement->errno . ': ' . $statement->error,
                $query->getDump($this->getName())
            );
            $statement->close();
            throw $e;
        }

        $data = [];

        /** @var Model $modelclass */
        $modelclass = $query->getModelClass();

        $data[Data::RESULT_MODEL_CLASS] = $modelclass;

        $data[Data::RESULT_ROWS] = $modelclass
            ? array_column($result->fetch_all(MYSQLI_ASSOC), null, $modelclass::getFieldName('/pk'))
            : $result->fetch_all(MYSQLI_ASSOC);

        $result->close();

        $data[DATA::NUM_ROWS] = $statement->num_rows;

        $statement->free_result();
        $statement->close();

        if (reset($query->getSqlPart()['select'])) {
            $result = $this->getConnection()->query('SELECT FOUND_ROWS()');
            $foundRows = $result->fetch_row();
            $result->close();
            $data[Data::FOUND_ROWS] = reset($foundRows);
        } else {
            $data[Data::FOUND_ROWS] = $data[DATA::NUM_ROWS];
        }

        $data[Data::QUERY_DUMP] = $query->getDump($this->getName());

        return $data;
    }

    /**
     * @param Query $query
     * @throws Exception
     * @return mysqli_stmt
     */
    private function getStatement(Query $query)
    {
        $statement = $this->getConnection()->prepare($query->getSql($this->getName()));

        if (!$statement) {
            throw new Exception(
                '#' . $this->getConnection()->errno . ': ' . $this->getConnection()->error,
                $query->getDump($this->getName())
            );
        }

        $binds = $query->getBinds();

        $types = '';
        foreach ($binds as $bind) {
            $types .= gettype($bind)[0];
        }

        $values = [str_replace('N', 's', $types)];

        if (!empty($types)) {
            $values = array_merge($values, $binds);

            if (call_user_func_array(array($statement, 'bind_param'), $this->makeValuesReferenced($values)) === false) {
                throw new Exception('Не удалось забиндить параметры', $query->getDump($this->getName()));
            }
        }

        return $statement;
    }

    /**
     * Get connection instance
     *
     * @param string|null $scheme
     * @return \Mysqli
     */
    public function getConnection($scheme = null)
    {
        return parent::getConnection();
    }

    private function makeValuesReferenced($arr)
    {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = & $arr[$key];
        }
        return $refs;
    }

    /**
     * @param Query $query
     * @throws Exception
     * @return array
     */
    public function insert(Query $query)
    {
        $statement = $this->getStatement($query);

        if ($statement->execute() === false) {
            $e = new Exception (
                'sql execute insert error #' . $statement->errno . ': ' . $statement->error,
                $query->getDump($this->getName())
            );

            $statement->close();

            throw $e;
        }

        $data = [];

        /** @var Model $modelclass */
        $modelclass = $query->getModelClass();

        $data[Data::RESULT_MODEL_CLASS] = $modelclass;
        $data[DATA::RESULT_ROWS] = $query->getValues();
        $data[DATA::AFFECTED_ROWS] = $statement->affected_rows;
        $data[DATA::INSERT_ID] = $statement->insert_id;

        if ($data[DATA::AFFECTED_ROWS] == 1) {
            $row = reset($data[DATA::RESULT_ROWS]);
            $row[$modelclass::getFieldName('/pk')] = $data[DATA::INSERT_ID];
            $data[DATA::RESULT_ROWS] = [$row];
        } else {
            throw new Exception('need testing multiinsert in one query');
        }

        $statement->close();

        return $data;
    }

    /**
     * @param Query $query
     * @throws Exception
     * @return array
     */
    public function update(Query $query)
    {
        $statement = $this->getStatement($query);

        if ($statement->execute() === false) {
            $e = new Exception (
                'sql execute update error #' . $statement->errno . ': ' . $statement->error,
                $query->getDump($this->getName())
            );

            $statement->close();

            throw $e;
        }

        $data = [];
//
//        $data[DATA::AFFECTED_ROWS] = $statement->affected_rows;
//        $data[DATA::INSERT_ID] = $statement->insert_id;

        $statement->close();

        return $data;
    }

    /**
     * @param Query $query
     * @throws Exception
     * @return array
     */
    public function delete(Query $query)
    {
        $statement = $this->getStatement($query);

        if ($statement->execute() === false) {
            $e = new Exception (
                'sql execute delete error #' . $statement->errno . ': ' . $statement->error,
                $query->getDump($this->getName())
            );

            $statement->close();

            throw $e;
        }

        $data = [];
//
//        $data[DATA::AFFECTED_ROWS] = $statement->affected_rows;
//        $data[DATA::INSERT_ID] = $statement->insert_id;

        $statement->close();

        return $data;
    }

    public function getDataScheme()
    {
        $dataScheme = [];

        $sql = 'SELECT * FROM information_schema.columns WHERE TABLE_SCHEMA="' . $this->getScheme() . '"';

        $result = $this->getConnection()->query($sql, MYSQLI_USE_RESULT);

        while ($row = $result->fetch_assoc()) {
            $columnName = $row['COLUMN_NAME'];
            $default = $default = $row['COLUMN_DEFAULT'];

            if (empty($default) && strstr($columnName, '__json') == '__json') {
                $default = '[]';
            }

            $dataScheme[$row['TABLE_NAME']][$columnName] =
                [
                    'type' => $row['COLUMN_TYPE'],
                    'nullable' => $row['IS_NULLABLE'] == 'YES',
                    'default' => $default,
                    'comment' => $row['COLUMN_COMMENT']
                ];
        }

        $result->close();

        return $dataScheme;
    }
}