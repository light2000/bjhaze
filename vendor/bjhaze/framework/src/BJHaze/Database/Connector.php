<?php
/**
 *
 * PDO Connection class.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Database;
use PDO;

class Connector
{

    /**
     * Master servers config
     *
     * @var array
     */
    protected $masters;

    /**
     * Slave servers config
     *
     * @var array
     */
    protected $slaves;

    /**
     * db username
     *
     * @var string
     */
    protected $username = '';

    /**
     * db password
     *
     * @var string
     */
    protected $password = '';

    /**
     * table name prefix
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * The master server key used in current request
     *
     * @var string
     */
    private $_masterKey;

    /**
     * The slave server key used in current request
     *
     * @var string
     */
    private $_slaveKey;

    /**
     *
     * @var array
     */
    protected $attributes = array(
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    /**
     * transaction level
     *
     * @var int
     */
    protected $transactions;

    /**
     * PDO class sets
     *
     * @var array
     */
    protected static $pdoClasses;

    /**
     *
     * @var \PDOStatement
     */
    private $_statement;

    /**
     * PDOStatement fetch mode
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    public function __construct (array $masters, array $slaves, $username, $password, $prefix = '', 
            array $attributes = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->prefix = $prefix;
        
        foreach ($masters as $master)
            $this->addServer($master);
        foreach ($slaves as $slave)
            $this->addServer($slave, true);
        
        if (! empty($attributes))
            $this->attributes = array_merge($this->attributes, $attributes);
        
        $this->_masterKey = array_rand($this->masters);
        if (! empty($this->slaves))
            $this->_slaveKey = array_rand($this->slaves);
    }

    /**
     * Add server
     *
     * @param mixed $server
     * @param string $slave
     * @return void
     */
    protected function addServer ($server, $slave = false)
    {
        if (is_string($server)) {
            $server = array(
                    'dsn' => $server,
                    'username' => $this->username,
                    'password' => $this->password
            );
        }
        
        $serverKey = self::generateServerKey($server['dsn'], $server['username'], 
                $server['password']);
        
        if ($slave)
            $this->slaves[$serverKey] = $server;
        else
            $this->masters[$serverKey] = $server;
    }

    /**
     * Generate server key
     *
     * @return string
     */
    public static function generateServerKey ()
    {
        return sprintf('%x', crc32(implode('', func_get_args())));
    }

    /**
     * Set PDOStatement fetch mode
     *
     * @param int $fetchMode
     */
    public function setFetchMode ($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }

    /**
     * Get the table prefix
     *
     * @return string
     */
    public function getPrefix ()
    {
        return $this->prefix;
    }

    /**
     * Fetch a PDO instance
     *
     * @param boolean $master
     * @return \PDO
     */
    public function getPdoInstance ($master = true)
    {
        if ($master || null === $this->_slaveKey || $this->transactions > 0) {
            if (! empty(self::$pdoClasses[$this->_masterKey]))
                return self::$pdoClasses[$this->_masterKey];
            else {
                $serverKey = $this->_masterKey;
                $conf = $this->masters[$serverKey];
            }
        } else {
            if (! empty(self::$pdoClasses[$this->_slaveKey]))
                return self::$pdoClasses[$this->_slaveKey];
            else {
                $serverKey = $this->_slaveKey;
                $conf = $this->slaves[$serverKey];
            }
        }
        
        return self::$pdoClasses[$serverKey] = new PDO($conf['dsn'], $conf['username'], 
                $conf['password'], $this->attributes);
    }

    /**
     * Get the prepared SQL statement
     *
     * @param string $sql
     * @param string $params
     * @param number $frequency
     * @return string
     */
    public function getSQLStatement ($sql, $params = null, $frequency = 1)
    {
        if ($this->prefix != '')
            $sql = preg_replace('/{{(.*?)}}/', $this->prefix . '\1', $sql);
        
        $sqls = array();
        
        if (null == $params)
            $sqls[] = $sql;
        elseif ($params instanceof \SplPriorityQueue) {
            while (! $params->isEmpty())
                $sqls[] = preg_replace_callback('/\?/', 
                        function  ($matches)
                        {
                            return $this->quoteValue($params->extract());
                        }, $sql);
        } elseif (is_array($params))
            if (! isset($params[0])) // the SQL use ':var' bind parameter can't use frequency
                $sqls[] = str_replace(array_keys($params), 
                        array_map(
                                array(
                                        $this,
                                        'quoteValue'
                                ), array_values($params)), $sql);
            else {
                $sqls = array();
                $paramsChunk = array_chunk($params, count($params) / $frequency);
                foreach ($paramsChunk as $parameters) {
                    $sqls[] = preg_replace_callback('/\?/', 
                            function  ($matches)
                            {
                                static $i = 0;
                                return $this->quoteValue($parameters[$i ++]);
                            }, $sql); // the SQL use '?' bind parameter
                }
            }
        
        return $sqls;
    }

    /**
     * Get SQL text
     *
     * @return mixed
     */
    public function fixPrefix ($sql)
    {
        if ($this->prefix != '')
            $sql = preg_replace('/{{(.*?)}}/', $this->prefix . '\1', $sql);
        
        return $sql;
    }

    /**
     * Prepares the SQL statement to be executed.
     *
     * @param boolean $master whether use master server
     * @throws PDOException if failed to prepare the SQL statement
     * @return void
     */
    public function prepare ($master, $sql)
    {
        $this->_statement = $this->getPdoInstance($master)->prepare($this->fixPrefix($sql));
    }

    /**
     * Binds a parameter to the SQL statement to be executed.
     *
     * @param mixed $name
     * @param mixed $value
     * @return void
     * @see http://www.php.net/manual/en/function.PDOStatement-bindParam.php
     */
    public function bindParam ($name, &$value)
    {
        $this->_statement->bindParam($name, $value, self::getPdoType(gettype($value)));
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $name
     * @param mixed $value
     * @return void
     * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
     */
    public function bindValue ($name, $value)
    {
        $this->_statement->bindValue($name, $value, self::getPdoType(gettype($value)));
    }

    /**
     * Execute the PDOStatement
     *
     * @return int
     */
    public function executeStatement ()
    {
        $this->_statement->execute();
        return $this->_statement->rowCount();
    }

    /**
     * Binds parameters and execute by batch
     *
     * @return void
     */
    public function bindValuesAndExecute ($params, $frequency = 1, $rowCount = false)
    {
        $batchSize = sizeof($params) / $frequency;
        $i = $n = 0;
        // parameter bound like :var1
        if (is_array($params) && ! isset($params[0]))
            foreach ($params as $name => $value) {
                $i ++;
                $this->bindValue($name, $value);
                if ($i == $batchSize) {
                    $this->_statement->execute();
                    $i = 0;
                    if ($rowCount)
                        $n += $this->_statement->rowCount();
                }
            }
        else
            foreach ($params as $name => $value) {
                $i ++;
                $this->bindValue($i, $value);
                if ($i == $batchSize) {
                    $this->_statement->execute();
                    $i = 0;
                    if ($rowCount)
                        $n += $this->_statement->rowCount();
                }
            }
        
        return $n;
    }

    /**
     * Executes the SQL statement use master server.
     * This method is meant only for executing update, insert, delete operation.
     *
     * @param string $sql
     * @param mixed $params
     * @return integer number of rows affected by the execution.
     * @throws PDOException execution failed
     */
    public function execute ($sql, $params = null, $frequency = 1)
    {
        $this->prepare(true, $sql);
        if (! empty($params)) {
            $n = $this->bindValuesAndExecute($params, $frequency, true);
        } else {
            $this->_statement->execute();
            $n = $this->_statement->rowCount();
        }
        $this->_statement = null;
        
        return $n;
    }

    /**
     * Executes the SQL statement and returns all rows.
     *
     * @param string $sql
     * @param mixed $params
     * @param boolean $master
     * @return mixed
     */
    public function queryAll ($sql, $params = null, $master = false)
    {
        return $this->queryInternal('fetchAll', $sql, $params, $master);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     *
     * @param string $sql
     * @param mixed $params
     * @param boolean $master
     * @return mixed
     */
    public function queryRow ($sql, $params = null, $master = false)
    {
        return $this->queryInternal('fetch', $sql, $params, $master);
    }

    /**
     * Executes the SQL statement and returns the value of the first column in
     * the first row of data.
     *
     * @param string $sql
     * @param mixed $params
     * @param boolean $master
     * @return mixed
     */
    public function queryScalar ($sql, $params = null, $master = false)
    {
        $result = $this->queryInternal('fetchColumn', $sql, $params, $master);
        if (is_resource($result) && get_resource_type($result) === 'stream')
            return stream_get_contents($result);
        else
            return $result;
    }

    /**
     * Executes the SQL statement, return by $method type (fetchAll, fetch, fetchColumn)
     *
     * @param string $method
     * @param string $sql
     * @param mixed $params
     * @param boolean $master
     * @return mixed
     */
    private function queryInternal ($method, $sql, $params, $master)
    {
        $this->prepare($master, $sql);
        $this->_statement->setFetchMode($this->fetchMode);
        if (! empty($params))
            $this->bindValuesAndExecute($params);
        else
            $this->_statement->execute();
        $result = $this->_statement->$method();
        
        $this->_statement->closeCursor();
        $this->_statement = null;
        
        return $result;
    }

    /**
     * Starts a transaction.
     *
     * @return void
     */
    public function beginTransaction ()
    {
        $this->transactions ++;
        return $this->getPdoInstance(true)->beginTransaction();
    }

    /**
     * Commit a transaction.
     *
     * @return boolean
     */
    public function commit ()
    {
        $this->transactions --;
        return $this->getPdoInstance(true)->commit();
    }

    /**
     * Rollback a transaction.
     *
     * @return boolean
     */
    public function rollback ()
    {
        return $this->getPdoInstance(true)->rollBack();
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string $sequenceName name of the sequence object (required by some
     *        DBMS)
     * @return string the row ID of the last row inserted, or the last value
     *         retrieved from the sequence object
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID ($sequenceName = '')
    {
        return $this->getPdoInstance(true)->lastInsertId($sequenceName);
    }

    /**
     * Get the current PDO driver
     *
     * @param boolean $master
     */
    public function getDriver ($master)
    {
        $serverDsn = $master || null === $this->_slaveKey || $this->transactions > 0 ? $this->masters[$this->_masterKey]['dsn'] : $this->slaves[$this->_slaveKey]['dsn'];
        
        return strstr($serverDsn, ':', true);
    }

    /**
     * Quotes a string value for use in a query.
     *
     * @param string $str string to be quoted
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValue ($str)
    {
        if (is_int($str) || is_float($str))
            return $str;
        $pdo = ! empty(self::$pdoClasses[$this->_slaveKey]) ? self::$pdoClasses[$this->_slaveKey] : (! empty(
                self::$pdoClasses[$this->_masterKey]) ? self::$pdoClasses[$this->_masterKey] : null);
        
        if (null !== $pdo && ($value = $pdo->quote($str)) !== false)
            return $value;
        else // The connection is not connect or the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
    }

    /**
     * Close Db connections in this class
     *
     * @return void
     */
    public function close ()
    {
        if (! empty($this->masters))
            foreach ($this->masters as $serverKey => $server)
                unset(self::$pdoClasses[$serverKey]);
        
        if (! empty($this->slaves))
            foreach ($this->slaves as $serverKey => $server)
                unset(self::$pdoClasses[$serverKey]);
    }

    /**
     * Close All Db connections
     *
     * @return void
     */
    public static function closeAll ()
    {
        self::$pdoClasses = null;
    }

    /**
     * Determines the PDO type for the specified PHP type.
     *
     * @param string $type The PHP type (obtained by gettype() call).
     * @return integer the corresponding PDO type
     */
    public static function getPdoType ($type)
    {
        static $map = array(
                'boolean' => PDO::PARAM_BOOL,
                'integer' => PDO::PARAM_INT,
                'string' => PDO::PARAM_STR,
                'resource' => PDO::PARAM_LOB,
                'NULL' => PDO::PARAM_NULL
        );
        return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
    }
}