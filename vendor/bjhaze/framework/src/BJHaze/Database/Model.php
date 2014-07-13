<?php
/**
 * This file contains Model Base class.
 * @author zhifeng <a_3722@hotmail.com>
 * 
 */
namespace BJHaze\Database;

use ReflectionMethod, Closure;
use BJHaze\Foundation\Container;

class ModelValidateException extends \RuntimeException
{
}

class Model extends Container
{

    /**
     * DB connection id
     *
     * @var string
     */
    protected $connection;

    /**
     * DB table
     *
     * @var string
     */
    protected $table;

    /**
     *
     * @var mixed
     */
    protected $primaryKey;

    /**
     * Get the field validations
     *
     * @return array
     */
    public function validations()
    {
        return array();
    }

    /**
     * Check the save data
     *
     * @param array $data            
     * @param string $action            
     * @return void
     */
    protected function doValidate(array $data, array $condition = null)
    {
        $validations = $this->validations();
        
        foreach ($validations as $key => $parameters) {
            if (isset($data[$key])) {
                $parameters = (array) $parameters;
                $validateMethod = 'validate' . array_shift($parameters);
                // fix validateUnique parameters here, so you don't need set it in validations()
                if (strtolower($validateMethod) == 'validateunique') {
                    $parameters[0] = $this->db->getConnection($this->connection)->getPdoInstance(true);
                    $parameters[1] = $this->db->getConnection($this->connection)->fixPrefix($this->table);
                    $parameters[2] = $key;
                    $parameters[3] = $condition;
                }
                if (method_exists($this->validator, $validateMethod))
                    if (! $this->validator->$validateMethod($data[$key], $parameters))
                        throw new ModelValidateException(sprintf('%s %s failed', $key, $validateMethod));
            }
        }
    }

    /**
     * Insert new record
     *
     * @param array $data            
     * @return int
     */
    public function create(array $data)
    {
        $this->doValidate($data);
        $ret = $this->db->insert($this->table, $data)->execute($this->connection);
        return $ret;
    }

    /**
     * Update record by primaryKey.
     *
     * @param array $data            
     * @return int
     */
    public function update(array $data)
    {
        if (is_string($this->primaryKey)) {
            if (! isset($data[$this->primaryKey]))
                throw new \LogicException('Model update method need data with primaryKey set');
            $condition = array(
                $this->primaryKey => $data[$this->primaryKey]
            );
            unset($data[$this->primaryKey]);
        } elseif (is_array($this->primaryKey)) {
            $priKeys = array_flip($this->primaryKey);
            $condition = array_intersect_key($data, $priKeys);
            $data = array_diff_key($data, $priKeys);
            if (sizeof($condition) != sizeof($this->primaryKey))
                throw new \LogicException('Model update method need data with all primaryKeys set');
        }
        
        $this->doValidate($data, $condition);
        
        return $this->db->update($this->table, $data)
            ->where($condition)
            ->execute($this->connection);
    }

    /**
     * Delete the record
     *
     * @param mixed $id            
     * @return int
     */
    public function delete($id)
    {
        if (is_int($id))
            return $this->db->delete($this->table)
                ->where($this->primaryKey . '= ?', $id)
                ->execute($this->connection);
        elseif (is_array($id))
            if (is_array($this->primaryKey))
                return $this->db->delete($this->table)
                    ->where($id)
                    ->execute($this->connection);
            else
                return $this->db->delete($this->table)
                    ->where($this->primaryKey . ' IN ?', $id)
                    ->execute($this->connection);
    }

    /**
     * Fetch record by primaryKey
     *
     * @param mixed $id            
     * @return int
     */
    public function read($id)
    {
        if (is_int($id))
            return $this->db->select()
                ->from($this->table)
                ->where($this->primaryKey . '= ?', $id)
                ->queryRow($this->connection);
        elseif (is_array($id))
            if (is_array($this->primaryKey))
                return $this->db->select()
                    ->from($this->table)
                    ->where($id)
                    ->queryRow($this->connection);
            else
                return $this->db->select()
                    ->from($this->table)
                    ->where($this->primaryKey . ' IN ?', $id)
                    ->queryRow($this->connection);
    }
}
