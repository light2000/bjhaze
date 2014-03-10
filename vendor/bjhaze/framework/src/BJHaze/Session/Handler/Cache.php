<?php
/**
 *
 * Cache Session
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Session\Handler;
use BJHaze\Cache\CacheInterface;

class Cache implements \SessionHandlerInterface
{

    /**
     * The cache repository instance.
     *
     * @var \BJHaze\Cache\CacheInterface
     */
    protected $cache;

    /**
     * The number of minutes to store the data in the cache.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cache driven handler instance.
     *
     * @param \BJHaze\Cache\CacheInterface $cache
     * @param int $minutes
     * @return void
     */
    public function __construct (CacheInterface $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
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
        return $this->cache->get($sessionId) ?  : '';
    }

    public function write ($sessionId, $data)
    {
        return $this->cache->set($sessionId, $data, $this->minutes * 60);
    }

    public function destroy ($sessionId)
    {
        return $this->cache->delete($sessionId);
    }

    public function gc ($lifetime)
    {
        return true;
    }

    /**
     * Get the underlying cache repository.
     *
     * @return \BJHaze\Cache\CacheInterface
     */
    public function getCache ()
    {
        return $this->cache;
    }
}