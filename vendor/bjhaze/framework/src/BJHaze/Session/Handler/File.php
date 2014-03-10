<?php
/**
 *
 * File Session
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Session\Handler;

class File extends \SessionHandler implements \SessionHandlerInterface
{

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new file driven handler instance.
     *
     * @param string $path
     * @return void
     */
    public function __construct ($path)
    {
        $this->path = $path;
    }

    public function open ($savePath, $sessionId)
    {
        return parent::open($this->path, $sessionId);
    }
}