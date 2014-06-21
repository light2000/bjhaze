<?php
use BJHaze\Database\Model;

class Category extends Model
{

    /**
     * DB connection id
     *
     * @var string
     */
    protected $connection = 'access';

    /**
     * DB table
     *
     * @var string
     */
    protected $table = '{{category}}';

    /**
     *
     * @var mixed
     */
    protected $primaryKey = 'id';

    /**
     * Fetch categories
     *
     * @param int $limit            
     * @return array
     */
    public function getList($limit = null)
    {
        $this->db->select('id', 'category_name')->from($this->table);
        if (null !== $limit)
            $this->db->limit($limit);
        
        return $this->db->queryAll($this->connection);
    }
}