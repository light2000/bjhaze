<?php
use BJHaze\Database\Model;

class Blog extends Model
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
    protected $table = '{{blog}}';

    /**
     *
     * @var mixed
     */
    protected $primaryKey = 'id';

    /**
     * Filter behaviors
     *
     * @var array
     */
    protected $before = array(
        'login'
    );

    public function validations()
    {
        return array(
            'addtime' => 'date'
        );
    }

    /**
     * Get blog list by page
     *
     * @param int $page            
     * @return array
     */
    public function getList($page, $pageSize = null)
    {
        if (null == $pageSize)
            $pageSize = $this['blog_page_size'];
        $offset = ($page - 1) * $pageSize;
        return $this->db->select('id', 'title', 'intro', 'addtime')
            ->from($this->table)
            ->order('id DESC')
            ->limit($pageSize, $offset)
            ->queryPaging($this->connection);
    }

    /**
     *
     * @param mixed $categoryID            
     */
    public function deletePostByCategoryId($categoryID)
    {
        return $this->db->delete($this->table)
            ->where('category_id IN ?', (array) $categoryID)
            ->execute($this->connection);
    }
}