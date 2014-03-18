<?php
/**
 *
 * Redis Cache.client from https://github.com/owlient/phpredis
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

class Redis implements CacheInterface
{

    /**
     * Redis server config
     *
     * @var array
     */
    protected $server;

    /**
     * Redis instance
     *
     * @var Redis
     */
    protected $redis;

    /**
     * Redis connections
     *
     * @var array
     */
    protected static $redisInstances;

    /**
     * Cache key prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Constructor
     *
     * @param array $server
     * @param string $prefix
     */
    public function __construct (array $server, $prefix)
    {
        $this->setServer($server);
        $this->prefix = $prefix;
    }

    /**
     * Set Redis server config
     *
     * @param array $server
     * @throws Exception
     */
    protected function setServer ($server)
    {
        static $def = array(
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 0,
                'pconnect' => false,
                'auth' => null
        );
        $this->server = array_merge($def, $server);
    }

    /**
     * Fetch a Redis instance
     *
     * @param string $server
     * @param int $port
     * @return Redis
     */
    public static function getRedisInstance ($server)
    {
        $serKey = sprintf('%x', crc32($server['host'] . $server['port']));
        if (empty(self::$redisInstances[$serKey])) {
            self::$redisInstances[$serKey] = new \Redis();
            if ($server['pconnect'])
                self::$redisInstances[$serKey]->pconnect($server['host'], $server['port'], 
                        $server['timeout']);
            else
                self::$redisInstances[$serKey]->connect($server['host'], $server['port'], 
                        $server['timeout']);
        }
        return self::$redisInstances[$serKey];
    }

    /**
     * Fetch a phpredis instance
     *
     * @return \Redis
     */
    public function getRedis ()
    {
        if (null === $this->redis) {
            $redis = self::getRedisInstance($this->server);
            if (! empty($this->server['db']))
                $redis->select($this->server['db']);
            if (! empty($this->server['auth']))
                $redis->auth($this->server['auth']);
            $this->redis = $redis;
        }
        return $this->redis;
    }

    public function set ($key, $value, $ttl = 0)
    {
        $this->getRedis()->setex($this->prefix . $key, $ttl, $value);
    }

    public function mset (array $item, $ttl = 0)
    {
        foreach ($item as $key => $value)
            $this->getRedis()->setex($this->prefix . $key, $ttl, $value);
    }

    public function get ($key)
    {
        return $this->getRedis()->get($this->prefix . $key);
    }

    public function mget (array $keys)
    {
        return $this->getRedis()->getMultiple(
                array_map(
                        function  ($key)
                        {
                            return $this->prefix . $key;
                        }, $keys));
    }

    public function increment ($key, $step = 1)
    {
        return $this->getRedis()->incrBy($this->prefix . $key, $step);
    }

    public function decrement ($key, $step = 1)
    {
        return $this->getRedis()->decrBy($this->prefix . $key, $step);
    }

    public function delete ($key)
    {
        return $this->getRedis()->del($this->prefix . $key);
    }

    public function flush ()
    {
        return $this->getRedis()->flushAll();
    }

    public function add ($key, $value, $ttl = 0)
    {
        $this->getRedis()->setnx($this->prefix . $key, $value);
        $this->getRedis()->expire($this->prefix . $key, $ttl);
    }
}
