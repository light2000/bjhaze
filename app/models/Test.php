<?php
use BJHaze\Database\Model;

class Test extends Model
{

    /**
     * DB connection id
     *
     * @var string
     */
    protected $connection = 'def';

    /**
     * DB table
     *
     * @var string
     */
    protected $table = '{{anti_word}}';

    /**
     *
     * @var mixed
     */
    protected $primaryKey = 'id';
    
    public function validations ()
    {
        return array('addtime' => 'date');
    }
}