<?php
/**
 *
 * Database Session
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Session\Handler;
use BJHaze\Database\Manager;

class Database implements \SessionHandlerInterface
{

    /**
     * The cache repository instance.
     *
     * @var string
     */
    protected $connection;

    /**
     * The number of minutes to store the data in the cache.
     *
     * @var int
     */
    protected $minutes;

    /**
     * The session Table
     *
     * @var string
     */
    protected $table;

    /**
     * The sessionId field name
     *
     * @var string
     */
    protected $idField;

    /**
     * The session expire time field name
     *
     * @var string
     */
    protected $expireField;

    /**
     * The session data field name
     *
     * @var string
     */
    protected $dataField;

    /**
     *
     * @var Manager
     */
    protected $db;

    /**
     * Create a new cache driven handler instance.
     *
     * @param string $connection
     * @param int $minutes
     * @param string $table
     * @param Manager $db
     */
    public function __construct ($connection, $minutes, $table, Manager $db, $idField = 'id', 
            $expireField = 'expire', $dataField = 'data')
    {
        $this->connection = $connection;
        $this->minutes = $minutes;
        $this->table = $table;
        $this->db = $db;
        $this->idField = $idField;
        $this->expireField = $expireField;
        $this->dataField = $dataField;
    }

    public function open ($savePath, $sessionName)
    {
        return true;
    }

    public function close ()
    {
        $this->db->close($this->connection);
    }

    public function read ($sessionId)
    {
        return $this->db->select($this->dataField)
            ->from($this->table)
            ->where($this->idField . ' = ?', $sessionId)
            ->queryScalar(false, $this->connection);
    }

    public function write ($sessionId, $data)
    {
        $expireTime = time() + $this->minutes * 60;
        try {
            $this->db->insert($this->table, 
                    array(
                            $this->idField => $sessionId,
                            $this->dataField => $data,
                            $this->expireField => $expireTime
                    ))
                ->onDuplicateUpdate("$this->dataField  = ?, $this->expireField = ? ", $data, 
                    $expireTime)
                ->execute($this->connection);
        } catch (\PDOException $ex) {
            $this->db->update($this->table, 
                    array(
                            $this->dataField => $data,
                            $this->expireField => $expireTime
                    ))
                ->where($this->idField . ' = ?', 
                    array(
                            $sessionId
                    ))
                ->execute($this->connection);
        }
    }

    public function destroy ($sessionId)
    {
        return $this->db->delete($this->table)->where($this->idField . ' = ?', 
                array(
                        $sessionId
                ));
    }

    public function gc ($lifetime)
    {
        return $this->db->delete($this->table)
            ->where($this->expireField . ' < :expire', 
                array(
                        'expire' => time()
                ))
            ->execute($this->connection);
    }
}