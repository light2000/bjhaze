<?php
/**
 *
 * Memcached Cache.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

class Memcached implements CacheInterface
{

    /**
     * Memcache server pool
     *
     * @var array
     */
    protected $servers;

    /**
     *
     * @var \Memcached
     */
    protected $memcached;

    /**
     * Cache key prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Constructor
     *
     * @param array $servers
     * @param string $prefix
     * @throws \RuntimeException
     */
    public function __construct (array $servers, $prefix)
    {
        if (! extension_loaded('memcached'))
            throw new \RuntimeException(
                    'BJHaze\Cache\Engine\Memcached requires PHP Memcached extension.');
        
        $this->memcached = new \Memcached();
        if (! empty($prefix))
            $this->memcached->setOption(\Memcached::OPT_PREFIX_KEY, $prefix);
        $this->memcached->addServers($servers);
    }

    /**
     *
     * @throws Exception
     * @return Memcached
     */
    public function getMemcached ()
    {
        return $this->memcached;
    }

    public function get ($key)
    {
        return $this->memcached->get($this->prefix . $key);
    }

    public function mget (array $keys)
    {
        return $this->memcached->getMulti(
                array_map(
                        function  ($key)
                        {
                            return $this->prefix . $key;
                        }, $keys));
    }

    public function mset (array $items, $ttl = 0)
    {
        return $this->memcached->setMulti(
                array_combine(
                        array_map(
                                function  ($key)
                                {
                                    return $this->prefix . $key;
                                }, array_keys($items)), $items), $ttl);
    }

    public function set ($key, $val, $ttl = 0)
    {
        return $this->memcached->set($this->prefix . $key, $val, $ttl);
    }

    public function add ($key, $val, $ttl = 0)
    {
        return $this->memcached->add($this->prefix . $key, $val, $ttl);
    }

    public function increment ($key, $step = 1)
    {
        return $this->memcached->increment($this->prefix . $key, $step);
    }

    public function decrement ($key, $step = 1)
    {
        return $this->memcached->decrement($this->prefix . $key, $step);
    }

    public function delete ($key, $ttl = 0)
    {
        return $this->memcached->delete($this->prefix . $key, $ttl);
    }

    public function flush ()
    {
        return $this->memcached->flush();
    }

    public function replace ($key, $val, $ttl = 0)
    {
        return $this->memcached->replace($this->prefix . $key, $val, $ttl);
    }

    public function cas ($casToken, $key, $val, $ttl = 0)
    {
        return $this->memcached->cas($casToken, $this->prefix . $key, $val, $ttl);
    }
}