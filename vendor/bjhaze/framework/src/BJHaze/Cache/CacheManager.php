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
     * Cache server configs sets
     *
     * @var array
     */
    protected $servers;

    /**
     * The default cache engine
     *
     * @var string
     */
    protected $engine;

    /**
     * Cache class sets
     *
     * @var array
     */
    protected $engines;

    /**
     * Constructor
     *
     * @param array $servers
     *            eg: engine => 'memcache', servers=>(..)
     * @param string $default            
     */
    public function __construct(array $servers, $default = null)
    {
        $this->engine = $default ?  : key($servers);
        $this->servers = $servers;
    }

    /**
     * Get the cache engine instance
     *
     * @throws \RuntimeException
     * @return CacheInterface
     */
    protected function getEngine()
    {
        $engine = $this->engine;
        if (! isset($this->engines[$engine])) {
            if (! empty($this->servers[$engine])) {
                $prefix = isset($this->servers[$engine]['prefix']) ? $this->servers[$engine]['prefix'] : '';
                switch ($this->servers[$engine]['engine']) {
                    case 'apc':
                        $this->engines[$engine] = new Engine\Apc($prefix);
                        break;
                    case 'memcache':
                        $this->engines[$engine] = new Engine\Memcache($this->servers[$engine]['servers'], $prefix);
                        break;
                    case 'memcached':
                        $this->engines[$engine] = new Engine\Memcached($this->servers[$engine]['servers'], $prefix);
                        break;
                    case 'redis':
                        $this->engines[$engine] = new Engine\Redis($this->servers[$engine]['server'], $prefix);
                        break;
                    default:
                        throw new \RuntimeException(sprintf("no cache engine named %s", $this->servers[$engine]['engine']), 500);
                }
            } else
                throw new \RuntimeException(sprintf("no cache config named %s", $engine), 500);
        }
        
        return $this->engines[$engine];
    }

    /**
     * Set the defalut cache config
     *
     * @param string $key            
     * @throws \RuntimeException
     */
    public function setEngine($key)
    {
        if (! empty($this->servers[$key])) {
            $this->engine = $key;
            return $this;
        } else
            throw new \RuntimeException(sprintf("no cache engine named %s", $key), 500);
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