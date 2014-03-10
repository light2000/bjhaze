<?php
/**
 *
 * Database Commander class.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Database;
use SplPriorityQueue;

class Commander
{

    const MSSQL_LIMIT_PRIOPOTY = 9;

    const INSERT_VALUES_PRIOROTY = 8;

    const JOIN_PRIOROTY = 7;

    const UPDATE_VALUES_PRIORITY = 6;

    const AFTER_FROM_JOIN_PRIOROTY = 5;

    const WHERE_PRIOPOTY = 4;

    const HAVING_PRIOPOTY = 3;

    const LIMIT_PRIOPOTY = 2;

    const OFFSET_PRIOPOTY = 1;

    const MSSQL_OFFSET_PRIOPOTY = 0;

    /**
     * Sql parameters bound
     *
     * @var array
     */
    protected $params;

    /**
     * Sql statement
     *
     * @var array
     */
    protected $query;

    /**
     * Execute times
     *
     * @var int
     */
    protected $frequency = 1;

    /**
     * Initialize SQL statement and parameters.
     */
    public function __construct ()
    {
        $this->params = new SplPriorityQueue();
    }

    /**
     * Bind the parameters to the SQL statement
     *
     * @param mixed $value
     * @param int $priority
     * @return void
     */
    protected function bindParam ($value, $priority)
    {
        if (is_array($value))
            foreach ($value as $val)
                $this->bindParam($val, $priority);
        else
            $this->params->insert($value, $priority);
    }

    /**
     * Sets the SELECT part of the query.
     *
     * @param array $columns
     * @return void
     */
    public function select ($columns = null)
    {
        if (null === $columns)
            $this->query['select'] = '*';
        elseif (is_array($columns))
            $this->query['select'] = implode(', ', $columns);
        else
            $this->query['select'] = (string) $columns;
    }

    /**
     * Sets the SELECT part of the query with the DISTINCT flag turned on.
     *
     * @param mixed $columns
     * @return void
     */
    public function selectDistinct ($columns = null)
    {
        $this->query['distinct'] = true;
        $this->select($columns);
    }

    /**
     * Sets the FROM part of the query.
     *
     * @param mixed $tables
     * @return void
     */
    public function from ($tables)
    {
        if (is_array($tables))
            $this->query['from'] = implode(', ', $tables);
        else
            $this->query['from'] = (string) $tables;
    }

    /**
     * Build SELECT part
     *
     * @param array $query
     * @throws \LogicException
     * @return string
     */
    protected function buildSelect ()
    {
        $sql = ! empty($this->query['distinct']) ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= $this->query['select'];
        if (! empty($this->query['from']))
            $sql .= ' FROM ' . $this->query['from'];
        else
            throw new \LogicException('Select query must contain the "from" portion.');
        $sql .= (string) $this->buildJoin();
        $sql .= (string) $this->buildWhere();
        $sql .= (string) $this->buildGroup();
        $sql .= (string) $this->buildOrder();
        $sql .= (string) $this->buildLimit();
        $sql .= (string) $this->buildOffset();
        
        return $sql;
    }

    /**
     * Build Mssql SELECT part
     *
     * @param array $query
     * @throws \LogicException
     * @return string
     */
    protected function buildMssqlSelect ()
    {
        $sql = ! empty($this->query['distinct']) ? 'SELECT DISTINCT ' : 'SELECT ';
        
        if (! empty($this->query['limit']) && empty($this->query['offset'])) {
            $sql .= 'TOP ? ';
            $this->bindParam($this->query['limit'], self::MSSQL_LIMIT_PRIOPOTY);
        }
        
        if (! empty($this->query['offset'])) {
            if (! isset($this->query['order']))
                $this->query['order'] = '(select 0)';
            $this->query['select'] .= ", row_number() over (order by {$this->query['order']}) as row_num";
        }
        
        $sql .= $this->query['select'];
        if (! empty($this->query['from']))
            $sql .= ' FROM ' . $this->query['from'];
        else
            throw new \LogicException('Select query must contain the "from" portion.');
        
        $sql .= $this->buildJoin();
        $sql .= $this->buildWhere();
        $sql .= $this->buildGroup();
        
        if (! empty($this->query['offset'])) {
            $start = $this->query['offset'] + 1;
            if ($this->query['limit'] > 0) {
                $finish = $this->query['offset'] + $this->query['limit'];
                $constraint = "between ? and ?";
                $this->bindParam($start, self::MSSQL_OFFSET_PRIOPOTY);
                $this->bindParam($finish, self::MSSQL_OFFSET_PRIOPOTY);
            } else {
                $this->bindParam($start, self::MSSQL_OFFSET_PRIOPOTY);
                $constraint = ">= ?";
            }
            $sql = "SELECT * from ({$sql}) as temp_table where row_num {$constraint}";
        } else {
            $sql .= $this->buildOrder();
        }
        
        return $sql;
    }

    /**
     * Sets the WHERE part of the query.
     *
     * @param mixed $condition
     * @param mixed $params
     * @return void
     */
    public function where ($condition, $params = null)
    {
        if (is_array($condition)) {
            $mergeCondition = array();
            foreach ($condition as $key => $value)
                if (is_array($value))
                    $mergeCondition[] = $key . ' IN ?';
                else
                    $mergeCondition[] = $key . ' = ?';
            return $this->where(implode(' AND ', $mergeCondition), $condition);
        } else {
            if (func_num_args() > 2)
                $params = array_slice(func_get_args(), 1);
            if (! empty($params))
                $this->fillInCondition($condition, (array) $params, self::WHERE_PRIOPOTY);
            if (empty($this->query['where']))
                $this->query['where'] = $condition;
            else
                $this->query['where'] .= ' AND ' . $condition;
        }
    }

    /**
     * Appends given condition to the existing WHERE part of the query with 'OR'
     * operator.
     *
     * @param string $condition
     * @param array $params
     * @return void
     */
    public function orWhere ($condition, array $params = null)
    {
        if (empty($this->query['where'])) {
            throw new \LogicException('or where can not put at the beginning the condition part');
        } else {
            if (! empty($params))
                $this->fillInCondition($condition, $params, self::WHERE_PRIOPOTY);
            $this->query['where'] .= ' OR ' . $condition;
        }
    }

    /**
     * Set conditions parameters
     *
     * @param string $condition
     * @param array $params
     * @return void
     */
    protected function fillInCondition (&$condition, array $params, $priority)
    {
        foreach ($params as $param)
            if (is_array($param)) {
                for ($i = 0, $count = sizeof($param), $placeholder = ''; $i < $count; $i ++) {
                    $placeholder .= ', ?';
                    $this->params->insert($param[$i], $priority);
                }
                $condition = preg_replace('/IN \(?\?(\)|^,)?/is', 
                        'IN ( ' . substr($placeholder, 1) . ' ) ', $condition, 1);
            } else
                $this->params->insert($param, $priority);
    }

    /**
     * Build WHERE part
     *
     * @param array $query
     */
    protected function buildWhere ()
    {
        return ! empty($this->query['where']) ? ' WHERE ' . $this->query['where'] : '';
    }

    /**
     * Appends an JOIN part to the query.
     *
     * @param string $table the table to be joined.
     * @param string $conditions the join condition.
     * @param array $params the parameters
     * @param string $type the join type
     * @return void
     */
    public function join ($type, $table, $condition = '', array $params = null)
    {
        if (! empty($params))
            $this->fillInCondition($condition, $params, 
                    ! empty($this->query['from']) ? self::AFTER_FROM_JOIN_PRIOROTY : self::JOIN_PRIOROTY);
        
        if ($condition != '')
            $condition = ' ON ' . $condition;
        
        $this->query['join'][] = $type . ' JOIN ' . $table . $condition;
    }

    /**
     * Build SQL statement JOIN part
     *
     * @return string
     */
    protected function buildJoin ()
    {
        return ! empty($this->query['join']) ? "\n" .implode("\n", $this->query['join']) : '';
    }

    /**
     * Sets the GROUP BY part of the query.
     *
     * @param mixed $columns the columns to be grouped by.
     * @return void
     */
    public function group ($columns)
    {
        if (is_string($columns))
            $this->query['group'] = $columns;
        else
            $this->query['group'] = implode(', ', $columns);
    }

    /**
     * Sets the HAVING part of the query.
     *
     * @param string $condition
     * @param array $params
     * @return void
     */
    public function having ($condition, $params = null)
    {
        $this->fillInCondition($condition, (array) $params, self::HAVING_PRIOPOTY);
        $this->query['having'] = $condition;
    }

    /**
     * Build GROUP part
     *
     * @param array $query
     * @return string
     */
    protected function buildGroup ()
    {
        if (! empty($this->query['group'])) {
            $sql = ' GROUP BY ' . $this->query['group'];
            if (! empty($this->query['having']))
                $sql .= ' HAVING ' . $this->query['having'];
            return $sql;
        }
    }

    /**
     * Sets the ORDER BY part of the query.
     *
     * @param mixed $columns
     * @return void
     */
    public function order ($columns)
    {
        if (is_array($columns))
            $this->query['order'] = implode(', ', $columns);
        else
            $this->query['order'] = $columns;
    }

    /**
     * Build SQL statement ORDER BY part
     *
     * @return string
     */
    protected function buildOrder ()
    {
        return ! empty($this->query['order']) ? ' ORDER BY ' . $this->query['order'] : '';
    }

    /**
     * Sets the LIMIT part of the query.
     *
     * @param int $limit the limit
     * @param int $offset the offset
     * @return void
     */
    public function limit ($limit, $offset = null)
    {
        $this->query['limit'] = (int) $limit;
        if ($offset !== null)
            $this->offset($offset);
    }

    /**
     * Sets the OFFSET part of the query.
     *
     * @param int $offset the offset
     * @return void
     */
    public function offset ($offset)
    {
        $this->query['offset'] = (int) $offset;
    }

    /**
     * Appends a SQL statement using UNION operator.
     *
     * @param string $sql the SQL statement to be appended using UNION
     * @return void
     */
    public function union ($sql)
    {
        $this->query['union'][] = $sql;
    }

    /**
     * Creates an INSERT SQL statement.
     *
     * @param string $table
     * @param array $columns
     * @return void
     */
    public function insert ($table, array $columns = null)
    {
        $this->query['insert'] = $table;
        if (! empty($columns)) {
            if (isset($columns[0]))
                if (! is_array($columns[0])) { // INSERT INTO table (column1, column2) SELECT...
                    $this->query['insertColumns'] = ' ( `' . implode('`,`', $columns) . '` ) ';
                    return;
                } else { // Batch insert
                    $this->query['insertColumns'] = array_keys($columns[0]);
                    $this->query['insertBatch'] = count($columns);
                }
            else
                $this->query['insertColumns'] = array_keys($columns);
            
            $this->bindParam($columns, self::INSERT_VALUES_PRIOROTY);
            
            $this->query['insertValues'] = ' ( ' .
                     substr(str_repeat(', ?', count($this->query['insertColumns'])), 1) . ' ) ';
            $this->query['insertColumns'] = ' ( `' . implode('`,`', $this->query['insertColumns']) .
                     '` ) ';
        }
    }

    /**
     * Creates an REPLACE INTO SQL statement.(Support MYSQL Only)
     *
     * @param string $table
     * @param array $columns
     * @return void
     */
    public function replace ($table, array $columns = null)
    {
        $this->insert($table, $columns);
        $this->query['replace'] = true;
    }

    /**
     * Creates an INSERT IGNORE INTO SQL statement.(Support MYSQL Only)
     *
     * @param string $table
     * @param array $columns
     * @return void
     */
    public function ignoreInsert ($table, array $columns = null)
    {
        $this->insert($table, $columns);
        $this->query['ignore'] = true;
    }

    /**
     * Use "ON DUPLICATE KEY UPDATE" After a Insert operation.(Support MYSQL Only)
     *
     * @param string $operation
     * @throws Exception
     * @return void
     */
    public function onDuplicateUpdate ($operation, $params)
    {
        $this->query['onDuplicateUpdate'] = array(
                $operation,
                $params
        );
    }

    /**
     * Build the insert SQL statement
     *
     * @return string
     */
    protected function buildInsert ()
    {
        $this->frequency = ! empty($this->query['insertBatch']) ? $this->query['insertBatch'] : 1;
        $sql = 'INSERT INTO ' . $this->query['insert'] . $this->query['insertColumns'];
        if (empty($this->query['insertValues']))
            if (! empty($this->query['select']))
                $sql .= ' ' . $this->buildSelect();
            else
                throw new \LogicException('INSERT SQL is not complete');
        else
            $sql .= ' VALUES ' . $this->query['insertValues'];
        
        return $sql;
    }

    /**
     * Build the Mysql insert SQL statement
     *
     * @return string
     */
    protected function buildMysqlInsert ()
    {
        $this->frequency = ! empty($this->query['insertBatch']) ? $this->query['insertBatch'] : 1;
        
        $sql = ! empty($this->query['replace']) ? 'REPLACE INTO ' : (! empty($this->query['ignore']) ? 'INSERT IGNORE INTO ' : 'INSERT INTO ');
        $sql .= $this->query['insert'] . $this->query['insertColumns'];
        if (empty($this->query['insertValues']))
            if (! empty($this->query['select']))
                $sql .= ' ' . $this->buildSelect();
            else
                throw new \LogicException('INSERT SQL is not complete');
        elseif ($this->frequency > 1) {
            $sql .= ' VALUES ' .
                     substr(str_repeat(', ' . $this->query['insertValues'], $this->frequency), 1);
            $this->frequency = 1; // mysql support patch insert
        } else
            $sql .= ' VALUES ' . $this->query['insertValues'];
        
        if (! empty($this->query['onDuplicateUpdate'])) {
            list ($operation, $params) = $this->query['onDuplicateUpdate'];
            $sql .= ' ON DUPLICATE KEY UPDATE ' . $operation;
            if (! empty($params))
                $this->bindParam($params, self::UPDATE_VALUES_PRIORITY);
        }
        
        return $sql;
    }

    /**
     * Creates an UPDATE SQL statement.
     *
     * @param string $table
     * @param mixed $col
     * @param mixed $value
     * @return void
     */
    public function update ($table, $column = null, $value = null)
    {
        $this->query['update'] = $table;
        $this->query['updateSets'] = array();
        if (is_array($column)) {
            $this->query['updateSets'] = array_map(
                    function  ($key)
                    {
                        return $key . ' = ?';
                    }, array_keys($column));
            $this->bindParam($column, self::UPDATE_VALUES_PRIORITY);
        } elseif (is_string($column)) {
            $this->query['updateSets'][] = $column;
            if (! empty($value))
                $this->bindParam(func_num_args() > 3 ? array_slice(func_get_args(), 2) : $value, 
                        self::UPDATE_VALUES_PRIORITY);
        }
    }

    /**
     * Self increment Update
     *
     * @param string $key
     * @param int $value
     * @throws LogicException
     * @return void
     */
    public function increment ($key, $value = 1)
    {
        if (empty($this->query['update']))
            throw new \LogicException('Increment must after update operation');
        $this->query['updateParts'][] = "$key = $key + ?";
        $this->bindParam($value, self::UPDATE_VALUES_PRIORITY);
    }

    /**
     * Self decrement Update
     *
     * @param string $key
     * @param int $value
     * @throws LogicException
     * @return void
     */
    public function decrement ($key, $value = 1)
    {
        if (empty($this->query['update']))
            throw new \LogicException('Decrement must after update operation');
        $this->query['updateParts'][] = "$key = $key - ?";
        $this->bindParam($value, self::UPDATE_VALUES_PRIORITY);
    }

    /**
     * Build UPDATE SQL statement.
     *
     * @return string
     */
    protected function buildUpdate ()
    {
        $sql = 'UPDATE ' . $this->query['update'];
        $sql .= ' SET ' . implode(', ', $this->query['updateSets']);
        $sql .= $this->buildWhere();
        return $sql;
    }

    /**
     * Build Mysql UPDATE SQL statement.
     *
     * @return string
     */
    protected function buildMysqlUpdate ()
    {
        $sql = 'UPDATE ' . $this->query['update'];
        $sql .= $this->buildJoin();
        $sql .= ' SET ' . implode(', ', $this->query['updateSets']);
        $sql .= $this->buildWhere();
        $sql .= $this->buildOrder();
        $sql .= $this->buildLimit();
        
        return $sql;
    }

    /**
     * Creates a DELETE SQL statement.
     *
     * @param string $table the table where the data will be deleted from.
     * @return void
     */
    public function delete ($table)
    {
        $this->query['delete'] = $table;
    }

    /**
     * Build DELETE SQL statement
     *
     * @param array $query
     * @return string
     */
    protected function buildDelete ()
    {
        if (! empty($this->query['from']))
            $sql = 'DELETE ' . $this->query['delete'] . ' FROM ' . $this->query['from'];
        else
            $sql = 'DELETE FROM ' . $this->query['delete'];
        $sql .= $this->buildWhere();
        
        return $sql;
    }

    /**
     * Build DELETE SQL statement
     *
     * @param array $query
     * @return string
     */
    protected function buildMysqlDelete ()
    {
        if (! empty($this->query['from']))
            $sql = 'DELETE ' . $this->query['delete'] . ' FROM ' . $this->query['from'];
        else
            $sql = 'DELETE FROM ' . $this->query['delete'];
        $sql .= $this->buildJoin();
        $sql .= $this->buildWhere();
        $sql .= $this->buildLimit();
        
        return $sql;
    }

    /**
     * Build SQL statement by query array
     *
     * @param string $driver pdo driver
     * @return string
     */
    public function buildQuery ($driver)
    {
        if (! empty($this->query['insert'])) {
            $driverBuild = "build{$driver}Insert";
            $sql = method_exists($this, $driverBuild) ? $this->$driverBuild() : $this->buildInsert();
        } elseif (! empty($this->query['select'])) {
            $driverBuild = "build{$driver}Select";
            $sql = method_exists($this, $driverBuild) ? $this->$driverBuild() : $this->buildSelect();
        } elseif (! empty($this->query['delete'])) {
            $driverBuild = "build{$driver}Delete";
            $sql = method_exists($this, $driverBuild) ? $this->$driverBuild() : $this->buildDelete();
        } elseif (! empty($this->query['update'])) {
            $driverBuild = "build{$driver}Update";
            $sql = method_exists($this, $driverBuild) ? $this->$driverBuild() : $this->buildUpdate();
        } else
            return null;
        
        $this->clearSQLStatement();
        
        return array(
                $sql,
                $this->params,
                $this->frequency
        );
    }

    /**
     * Build limit part
     *
     * @param array $query
     * @return string
     */
    protected function buildLimit ()
    {
        if (! empty($this->query['limit'])) {
            $this->bindParam($this->query['limit'], self::LIMIT_PRIOPOTY);
            return " LIMIT ?";
        }
        return '';
    }

    /**
     * Build offset part
     *
     * @param array $conditions
     */
    protected function buildOffset ()
    {
        if (! empty($this->query['offset'])) {
            $this->bindParam($this->query['offset'], self::OFFSET_PRIOPOTY);
            return " OFFSET ?";
        }
        return '';
    }

    /**
     * Reset the DB SQL parts
     *
     * @return void
     */
    public function clearSQLStatement ()
    {
        $this->query = array();
    }
}