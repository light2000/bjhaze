<?php
/**
 *
 * Cache Manager.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache;

class CacheManager implements Engine\CacheInterface
{

    /**
     * The default cache engine
     *
     * @var string
     */
    protected $engine;

    /**
     * The cache key prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Cache class sets
     *
     * @var array
     */
    protected $servers;

    /**
     * Constructor
     *
     * @param array $servers
     *            eg: driver => 'memcache', servers=>(..)
     * @param string $prefix            
     * @param string $default            
     */
    public function __construct(array $servers, $keyPrefix = '', $default = null)
    {
        $this->prefix = $keyPrefix;
        $this->engine = $default ?  : key($servers);
        $this->servers = $servers;
    }

    /**
     * Get the cache engine instance
     *
     * @param string $key            
     * @return CacheInterface
     */
    public function getEngine($key = NULL)
    {
        if (null == $key)
            $key = $this->engine;
        if (! empty($this->servers[$key])) {
            if (is_array($this->servers[$key])) {
                switch ($this->servers[$key]['driver']) {
                    case 'apc':
                        $this->servers[$key] = new Engine\Apc($this->prefix);
                        break;
                    case 'memcache':
                        $this->servers[$key] = new Engine\Memcache($this->servers[$key]['servers'], $this->prefix);
                        break;
                    case 'memcached':
                        $this->servers[$key] = new Engine\Memcached($this->servers[$key]['servers'], $this->prefix);
                        break;
                    case 'redis':
                        $this->servers[$key] = new Engine\Redis($this->servers[$key]['server'], $this->prefix);
                }
            }
            
            return $this->servers[$key];
        }
    }

    public function set($key, $value, $ttl = 0)
    {
        return $this->getEngine()->set($key, $value, $ttl);
    }

    public function mset(array $item, $ttl = 0)
    {
        return $this->getEngine()->mset($item, $ttl);
    }

    public function get($key)
    {
        return $this->getEngine()->get($key);
    }

    public function mget(array $keys)
    {
        return $this->getEngine()->mget($keys);
    }

    public function increment($key, $step = 1)
    {
        return $this->getEngine()->inc($key, $step);
    }

    public function decrement($key, $step = 1)
    {
        return $this->getEngine()->dec($key, $step);
    }

    public function delete($key)
    {
        return $this->getEngine()->delete($key);
    }

    public function flush()
    {
        return $this->getEngine()->flush();
    }
}