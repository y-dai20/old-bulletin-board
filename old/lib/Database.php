<?php

require_once('function.php');
require_once('config/database.php');

class Database
{
    protected $dbh;
    protected $table_name;
    protected $validate_rules = [];

    public function __construct()
    {
        $this->dbh = new PDO(DSN, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
    }

    public function select(
        string $where     = null,
        array $where_args = [],
        string $order     = null,
        int $limit        = null,
        int $offset       = null,
        array $columns    = ['*']
    )
    {
        $sql  = 'SELECT ' . implode(',', $columns);
        $sql .= ' FROM ' . $this->table_name;

        if (!is_empty($where) && !is_empty($where_args)) {
            $sql .=  ' WHERE ' . $where;
        }

        if (!is_empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }

        if (!is_empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        if (!is_empty($offset)) {
            $sql .= ' OFFSET ' . $offset;
        }

        $stmt = $this->dbh->prepare($sql);

        if (!is_empty($where) && !is_empty($where_args)) {
            $this->bindValues($stmt, $where_args);
        }

        $stmt->execute();

        return $stmt;
    }

    public function count(string $where = '', array $where_args = [])
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table_name;

        if (!is_empty($where) && !is_empty($where_args)) {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $this->dbh->prepare($sql);

        if (!is_empty($where) && !is_empty($where_args)) {
            $this->bindValues($stmt, $where_args);
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function insert(array $inputs = [])
    {
        if (!is_empty($inputs['password'])) {
            $hashed_password = password_hash($inputs['password'], PASSWORD_DEFAULT);
        } else {
            $hashed_password = null;
        }
        $inputs['password'] = $hashed_password;

        $sql  = 'INSERT INTO ' . $this->table_name . ' (' . implode(',', array_keys($inputs)) . ') VALUES (:' . implode(',:', array_keys($inputs)) . ')';
        $stmt = $this->dbh->prepare($sql);
        $this->bindValues($stmt, $inputs);
        $stmt->execute();

    }

    public function delete(string $where, array $where_args = [])
    {
        $sql  = 'DELETE FROM ' . $this->table_name . ' WHERE ' . $where;
        $stmt = $this->dbh->prepare($sql);
        $this->bindValues($stmt, $where_args);
        $stmt->execute();
    }

    public function update(array $inputs = [], string $where, array $where_args = [])
    {
        $sql  = 'UPDATE ' . $this->table_name . ' SET ' . implode(',', $this->getPlaceHolders($inputs)) . ' WHERE ' . $where;

        $stmt = $this->dbh->prepare($sql);
        $this->bindValues($stmt, $inputs);
        $this->bindValues($stmt, $where_args);

        $stmt->execute();
    }

    public function bindValues($stmt,array $args)
    {
        foreach ($args as $key => $value) {
            $stmt->bindValue(":{$key}", $value, $this->getParameterType($value));
        }
    }

    protected function getPlaceHolders(array $parameters)
    {
        $place_holders = [];
        foreach ($parameters as $parameter_key => $parameter_value) {
            $place_holders[] = "{$parameter_key} = :{$parameter_key}";
        }

        return $place_holders;
    }

    protected function getParameterType($parameter)
    {
        $type = gettype($parameter);
        if ($type === 'integer') {
            return PDO::PARAM_INT;
        } elseif ($type === 'string') {
            return PDO::PARAM_STR;
        } elseif ($type === 'boolean') {
            return PDO::PARAM_BOOL;
        }

        return PDO::PARAM_NULL;
    }

    public function getValidateRules() {
        return $this->validate_rules;
    }
}
