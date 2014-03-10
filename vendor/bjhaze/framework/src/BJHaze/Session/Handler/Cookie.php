<?php
/**
 *
 * Cookie Session
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Session\Handler;
use BJHaze\Encryption\Encrypter;

class Cookie implements \SessionHandlerInterface
{

    /**
     * The cookie jar instance.
     *
     * @var int
     */
    protected $minutes;

    /**
     *
     * @var \BJHaze\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Cookie domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Cookie secure
     *
     * @var boolean
     */
    protected $secure;

    /**
     * Cookie httponly
     *
     * @var boolean
     */
    protected $httponly;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param int $minutes
     * @param string $encryptionKey
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     */
    public function __construct ($minutes, $encryptionKey = null, $domain = null, $secure = false, 
            $httponly = true)
    {
        $this->minutes = $minutes;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httponly = $httponly;
        if (! empty($encryptionKey))
            $this->encrypter = new Encrypter($encryptionKey);
    }

    public function open ($savePath, $sessionName)
    {
        return true;
    }

    public function close ()
    {
        return true;
    }

    public function read ($sessionId)
    {
        if (empty($_COOKIE[$sessionId]))
            return false;
        elseif (! empty($this->encrypter))
            return $this->encrypter->decrypt($_COOKIE[$sessionId]);
        else
            return $_COOKIE[$sessionId];
    }

    public function write ($sessionId, $data)
    {
        if (! empty($this->encrypter))
            $data = $this->encrypter->encrypt($data);
        setcookie($sessionId, $data, $this->minutes * 60 + time(), '/', $this->domain, 
                $this->secure, $this->httponly);
    }

    public function destroy ($sessionId)
    {
        setcookie($sessionId, null, - 2628000, '/', $this->domain, $this->secure, $this->httponly);
    }

    public function gc ($lifetime)
    {
        return true;
    }
}