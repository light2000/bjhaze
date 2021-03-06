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
    public function getList($page, $pageSize = null, $categoryID = null)
    {
        if (null == $pageSize)
            $pageSize = $this['blog_page_size'];
        $offset = ($page - 1) * $pageSize;
        $this->db->select('id', 'title', 'intro', 'addtime')
            ->from($this->table)
            ->order('id DESC')
            ->limit($pageSize, $offset);
        if (! empty($categoryID))
            $this->db->where('category_id = ?', $categoryID);
        return $this->db->queryPaging($this->connection);
    }
}