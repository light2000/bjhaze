<?php
use BJHaze\Database\Model;

class User extends Model
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
    protected $table = '{{user}}';

    /**
     *
     * @var mixed
     */
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->session->start();
    }

    /**
     * User login
     *
     * @param string $username            
     * @param string $password            
     * @return boolean
     */
    public function login($username, $password)
    {
        $user = $this->db->select('id', 'last_login_time', 'last_login_ip')
            ->from($this->table)
            ->where('username = ? AND password = ?', $username, $password)
            ->queryRow($this->connection);
        if (! empty($user)) {
            $this->update(array(
                'id' => $user['id'],
                'last_login_time' => date("Y-m-d H:i:s"),
                'last_login_ip' => $this->request->getUserHostAddress()
            ));
            
            $this->session->set('uid', $user['id']);
            $this->session->set('username', $username);
            $this->session->set('last_login_time', $user['last_login_time']);
            $this->session->set('last_login_ip', $user['last_login_ip']);
            $this->session->set('login_time', time());
            return true;
        }
        
        return false;
    }

    /**
     * check login status
     *
     * @return boolean
     */
    public function isLogin()
    {
        return null !== $this->session->get('uid');
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        $this->session->destroy();
    }
}