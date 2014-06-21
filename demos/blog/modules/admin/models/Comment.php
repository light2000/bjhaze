<?php
use BJHaze\Database\Model;

class Comment extends Model
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
    protected $table = '{{comment}}';

    /**
     *
     * @var mixed
     */
    protected $primaryKey = 'id';

    /**
     *
     * @param int $page            
     * @param int $pageSize            
     * @param int $blogId            
     * @return array
     */
    public function getList($page = 1, $pageSize = null, $blogId = null)
    {
        if (null === $pageSize)
            $pageSize = $this['comment_page_size'];
        $offset = ($page - 1) * $pageSize;
        $this->db->select("{$this->table}.id", "{{blog}}.title", 'user_ip', 'blog_id', "{$this->table}.content", "{$this->table}.addtime")
            ->from($this->table)
            ->leftJoin('{{blog}}', "{$this->table}.blog_id = {{blog}}.id")
            ->order($this->table . '.id DESC')
            ->limit($pageSize, $offset);
        if (null !== $blogId)
            $this->db->where('blog_id = ?', $blogId);
        
        return $this->db->queryPaging($this->connection);
    }
}