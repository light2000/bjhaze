<?php
/**
 *
 * Session Manager
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Session;

use SessionHandlerInterface;

class Manager
{

    protected $boot = false;

    protected $sessionHandler;

    public function __construct(SessionHandlerInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * Session start
     *
     * @return void
     */
    public function start()
    {
        if ($this->boot)
            return;
        session_set_save_handler($this->sessionHandler, true);
        session_start();
        $this->boot = true;
    }

    /**
     * Session destroy
     *
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * Session set
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Session get
     *
     * @param string $key            
     * @return mixed
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * get session data
     *
     * @param string $key            
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Session delete
     *
     * @param string $key            
     */
    public function delete($key)
    {
        unset($_SESSION[$key]);
    }
}