<?php
/**
 *
 * Memcache Cache.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

class Memcache implements CacheInterface
{

    private $_compressed;

    /**
     * memcache server pool
     *
     * @var array
     */
    protected $servers;

    /**
     *
     * @var Memcache
     */
    protected $memcache;

    /**
     * Cache key prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Whether have active memcache server
     *
     * @var boolean
     */
    protected $enabled;

    public function __construct (array $servers, $prefix)
    {
        if (! extension_loaded('memcache'))
            throw new \LogicException(
                    'BJHaze\Cache\Engine\Memcache requires PHP memcache extension.');
        $this->enabled = true;
        $this->prefix = $prefix;
        $this->memcache = new \Memcache();
        $this->addServers($servers);
    }

    /**
     * Set Compressed status
     *
     * @param unknown $compressed
     */
    public function setCompressed ($compressed)
    {
        if (! empty($this->memcache))
            throw new \LogicException('You can not call this method when memcache is initialized');
        $this->_compressed = (bool) $compressed;
    }

    /**
     * Get whether use memcache compressed
     *
     * @return boolean
     */
    public function getCompressed ()
    {
        return $this->_compressed ? MEMCACHE_COMPRESSED : 0;
    }

    /**
     * Add serverto the memcache server pool
     *
     * @param array $servers
     */
    public function addServers (array $servers)
    {
        $defServer = array(
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => false,
                'weight' => 1,
                'timeout' => 0.01,
                'retry_interval' => 45,
                'status' => false,
                'failure_callback' => function  ()
                {}
        );
        
        foreach ($servers as $server) {
            $server = array_merge($defServer, $server);
            $ret = $this->memcache->addServer($server['host'], $server['port'], 
                    $server['persistent'], $server['weight'], $server['timeout'], 
                    $server['retry_interval'], $server['status'], $server['failure_callback']);
        }
    }

    /**
     * Whether have active memcache server
     *
     * @return boolean
     */
    public function getEnabled ()
    {
        return $this->enabled;
    }

    /**
     * Get the memcache client instance
     *
     * @throws Exception
     */
    private function getMemcache ()
    {
        return $this->memcache;
    }

    public function get ($key)
    {
        return $this->memcache->get($this->prefix . $key);
    }

    public function mget (array $keys)
    {
        return $this->memcache->get(
                array_map(
                        function  ($key)
                        {
                            return $this->prefix . $key;
                        }, $keys));
    }

    public function set ($key, $val, $ttl = 0)
    {
        return $this->memcache->set($this->prefix . $key, $val, $this->getCompressed(), $ttl);
    }

    public function mset (array $items, $ttl = 0)
    {
        $rt = true;
        foreach ($items as $key => $value)
            $rt &= $this->set($this->prefix . $key, $value, $ttl);
        return $rt;
    }

    public function increment ($key, $step = 1)
    {
        return $this->getMemcache()->increment($this->prefix . $key, $step);
    }

    public function decrement ($key, $step = 1)
    {
        return $this->getMemcache()->decrement($this->prefix . $key, $step);
    }

    public function delete ($key)
    {
        return $this->getMemcache()->delete($this->prefix . $key);
    }

    public function flush ()
    {
        return $this->getMemcache()->flush();
    }

    public function add ($key, $val, $ttl = 0)
    {
        return $this->getMemcache()->add($this->prefix . $key, $val, $this->getCompressed(), $ttl);
    }

    public function replace ($key, $val, $ttl = 0)
    {
        return $this->getMemcache()->replace($this->prefix . $key, $val, $this->getCompressed(), 
                $ttl);
    }
}