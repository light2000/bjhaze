<?php
/**
 *
 * DB manager class.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Database;

class Manager
{

    /**
     * connections
     *
     * @var array
     */
    protected $connections;

    /**
     * Default connection
     *
     * @var string
     */
    protected $defaultConnection;

    /**
     * Database query builder
     *
     * @var \BJHaze\Database\Commander
     */
    protected $commander;

    public function __construct (array $connections, $defaultConnection = null)
    {
        static $def = array(
                'masters' => null,
                'slaves' => null,
                'username' => '',
                'password' => '',
                'prefix' => null,
                'attributes' => null
        );
        foreach ($connections as $key => $connection)
            $this->connections[$key] = array_merge($def, $connection);
        
        $this->defaultConnection = $defaultConnection ?  : key($connection);
        $this->commander = new Commander();
    }

    /**
     * Create a connection instance
     *
     * @param array $config
     */
    protected function buildConnection ($conf)
    {
        return new Connector($conf['masters'], $conf['slaves'], $conf['username'], $conf['password'], 
                $conf['prefix'], $conf['attributes']);
    }

    /**
     * Get Connector by key
     *
     * @param string $connection
     * @return \BJHaze\Database\Connector
     */
    public function getConnection ($connection = null)
    {
        if (null == $connection)
            $connection = $this->defaultConnection;
        
        if (! empty($this->connections[$connection]))
            if (is_array($this->connections[$connection]))
                return $this->connections[$connection] = $this->buildConnection(
                        $this->connections[$connection]);
            else
                return $this->connections[$connection];
    }

    /**
     * Close db connections
     *
     * @return void
     */
    public function close ($connection = null)
    {
        if (empty($connection))
            foreach ($this->bindings as $connection)
                $connection->close();
        else
            Connector::closeAll();
    }

    /**
     * Get current SQL statement build in Commander
     *
     * @param string $connection
     * @param boolean $master
     * @throws \LogicException
     * @return array
     */
    protected function getCommanderQuery ($connection, $master)
    {
        if (null != ($query = $this->commander->buildQuery(
                $this->getConnection($connection)
                    ->getDriver($master))))
            return $query;
        else
            throw new \LogicException('No SQL statement build yet');
    }

    public function getSQLStatement ($connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, true);
        
        return $this->getConnection($connection)->getSQLStatement($sql, $params, $frequency);
    }

    /**
     * Executes the SQL statement use master server.
     *
     * @param string $connection
     * @return integer rows affected.
     */
    public function execute ($connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, true);
        
        return $this->getConnection($connection)->execute($sql, $params, $frequency);
    }

    /**
     * Get result with count
     *
     * @param string $master
     * @param string $connection
     * @return multitype:
     */
    public function queryPaging ($master = false, $connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, $master);
        
        $result = $this->getConnection($connection)->queryAll($sql, clone $params, $master);
        
        $count = $this->getConnection($connection)->queryScalar(
                preg_replace('/SELECT (.*?) FROM/is', 'SELECT COUNT(*) FROM', $sql), $params, 
                $master);
        
        return compact('result', 'count');
    }

    /**
     * Executes the SQL statement and returns all rows.
     *
     * @param boolean $master whether use master server
     * @param string $connection
     * @return array
     */
    public function queryAll ($master = false, $connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, $master);
        
        return $this->getConnection($connection)->queryAll($sql, $params, $master);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     *
     * @param boolean $master whether use master server
     * @param string $connection
     * @return mixed
     */
    public function queryRow ($master = false, $connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, $master);
        
        return $this->getConnection($connection)->queryRow($sql, $params, $master);
    }

    /**
     * Executes the SQL statement and returns the value of the first column in
     * the first row of data.
     *
     * @param boolean $master whether use master server
     * @return mixed
     */
    public function queryScalar ($master = false, $connection = null)
    {
        list ($sql, $params, $frequency) = $this->getCommanderQuery($connection, $master);
        
        return $this->getConnection($connection)->queryScalar($sql, $params, $master);
    }

    /**
     * Sets the SELECT part of the query.
     *
     * @param array $columns
     * @return \BJHaze\Database\Manager
     */
    public function select ($columns = null)
    {
        if (func_num_args() > 1)
            $columns = func_get_args();
        $this->commander->select($columns);
        
        return $this;
    }

    /**
     * Sets the SELECT part of the query with the DISTINCT flag turned on.
     *
     * @param mixed $columns
     * @return \BJHaze\Database\Manager
     */
    public function selectDistinct ($columns = null)
    {
        if (func_num_args() > 1)
            $columns = func_get_args();
        $this->commander->selectDistinct($columns);
        
        return $this;
    }

    /**
     * Sets the FROM part of the query.
     *
     * @param mixed $tables
     * @return \BJHaze\Database\Manager
     */
    public function from ($tables)
    {
        if (func_num_args() > 1)
            $tables = func_get_args();
        $this->commander->from($tables);
        
        return $this;
    }

    /**
     * Sets the WHERE part of the query.
     *
     * @param string $condition
     * @param mixed $params
     * @return \BJHaze\Database\Manager
     */
    public function where ($condition, $params = null)
    {
        if (func_num_args() > 1) {
            $params = array_slice(func_get_args(), 1);
            if (substr_count($condition, '?') != count($params))
                throw new \LogicException('SQL build error: where "?" number not match parameters');
        }
        $this->commander->where($condition, $params);
        
        return $this;
    }

    /**
     * Appends given condition to the existing WHERE part of the query with 'OR'
     * operator.
     *
     * @param string $condition
     * @param array $params
     * @return \BJHaze\Database\Manager
     */
    public function orWhere ($condition, $params = null)
    {
        if (func_num_args() > 1) {
            $params = array_slice(func_get_args(), 1);
            if (substr_count($condition, '?') != count($params))
                throw new \LogicException('SQL build error: orWhere "?" number not match parameters');
        }
        $this->commander->orWhere($condition, $params);
        
        return $this;
    }

    /**
     * Appends an INNER JOIN part to the query.
     *
     * @param string $table
     * @param string $condition
     * @param mixed $params
     * @return \BJHaze\Database\Manager
     */
    public function join ($type, $table, $condition = '', $params = null)
    {
        if (func_num_args() > 3) {
            $params = array_slice(func_get_args(), 3);
            if (substr_count($condition, '?') != count($params))
                throw new \LogicException('SQL build error: join "?" number not match parameters');
        }
        $this->commander->join($type, $table, $condition, $params);
        
        return $this;
    }

    /**
     * Appends a LEFT OUTER JOIN part to the query.
     *
     * @param string $table
     * @param mixed $conditions
     * @param array $params
     * @return \BJHaze\Database\Manager
     */
    public function leftJoin ($table, $condition, $params = null)
    {
        if (func_num_args() > 2) {
            $params = array_slice(func_get_args(), 2);
            if (substr_count($condition, '?') != count($params))
                throw new \LogicException('SQL build error: join "?" number not match parameters');
        }
        $this->commander->join('LEFT', $table, $condition, $params);
        
        return $this;
    }

    /**
     * Sets the GROUP BY part of the query.
     *
     * @param mixed $columns
     * @return \BJHaze\Database\Manager
     */
    public function group ($columns)
    {
        if (func_num_args() > 1)
            $columns = func_get_args();
        $this->commander->group($columns);
        
        return $this;
    }

    /**
     * Sets the HAVING part of the query.
     *
     * @param string $condition
     * @param array $params
     * @return \BJHaze\Database\Manager
     */
    public function having ($condition, $params = null)
    {
        if (func_num_args() > 2)
            $params = array_slice(func_get_args(), 1);
        $this->commander->having($condition, $params);
        
        return $this;
    }

    /**
     * Sets the ORDER BY part of the query.
     *
     * @param mixed $columns.
     * @return \BJHaze\Database\Manager
     */
    public function order ($columns)
    {
        if (func_num_args() > 1)
            $columns = func_get_args();
        $this->commander->order($columns);
        
        return $this;
    }

    /**
     * Sets the LIMIT part of the query.
     *
     * @param int $limit
     * @param int $offset
     * @return \BJHaze\Database\Manager
     */
    public function limit ($limit, $offset = null)
    {
        $this->commander->limit($limit, $offset);
        
        return $this;
    }

    /**
     * Sets the OFFSET part of the query.
     *
     * @param int $offset
     * @return \BJHaze\Database\Manager
     */
    public function offset ($offset)
    {
        $this->commander->offset($offset);
        
        return $this;
    }

    /**
     * Appends a SQL statement using UNION operator.
     *
     * @param string $sql
     * @return \BJHaze\Database\Manager
     */
    public function union ($sql)
    {
        $this->commander->union($sql);
        
        return $this;
    }

    /**
     * Creates an INSERT SQL statement.
     *
     * @param string $table
     * @param array $columns
     * @return \BJHaze\Database\Manager
     */
    public function insert ($table, array $columns = null)
    {
        $this->commander->insert($table, $columns);
        
        return $this;
    }

    /**
     * Creates an UPDATE SQL statement.
     *
     * @param string $table
     * @param array $columns
     * @return \BJHaze\Database\Manager
     */
    public function update ($table, $column = null, $value = null)
    {
        $this->commander->update($table, $column, $value);
        
        return $this;
    }

    /**
     * Self increment
     *
     * @param string $key
     * @param int $value
     * @throws LogicException
     * @return \BJHaze\Database\Manager
     */
    public function increment ($key, $value = 1)
    {
        $this->commander->increment($key, $value);
        
        return $this;
    }

    /**
     * Self decrement Update
     *
     * @param string $key
     * @param int $value
     * @throws LogicException
     * @return \BJHaze\Database\Manager
     */
    public function decrement ($key, $value = 1)
    {
        $this->commander->decrement($key, $value);
        
        return $this;
    }

    /**
     * Creates a DELETE SQL statement.
     *
     * @param string $table
     * @return \BJHaze\Database\Manager
     */
    public function delete ($table)
    {
        $this->commander->delete($table);
        
        return $this;
    }

    /**
     * Use "ON DUPLICATE KEY UPDATE" After a Insert operation.(Support MYSQL Only)
     *
     * @param string $operation
     * @param mixed $params
     * @return \BJHaze\Database\Manager
     */
    public function onDuplicateUpdate ($operation, $params = null)
    {
        if (func_num_args() > 1)
            $params = array_slice(func_get_args(), 1);
        $this->commander->onDuplicateUpdate($operation, $params);
        
        return $this;
    }
}