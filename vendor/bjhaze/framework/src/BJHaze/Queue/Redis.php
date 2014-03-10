<?php
/**
 *
 * Redis Queue. client from https://github.com/owlient/phpredis
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Queue;
use Redis;
use BJHaze\Cache\Redis as CacheRedis;

class Redis implements QueueInterface
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
     * Queue key
     *
     * @var string
     */
    protected $queueKey;

    /**
     * Constructor
     *
     * @param string $queueKey
     * @param array $server
     */
    public function __construct ($queueKey, array $server)
    {
        $this->queueKey = $queueKey;
        $this->setServer($server);
    }

    /**
     * Set Redis server config
     *
     * @param array $server
     * @throws Exception
     */
    public function setServer ($server)
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
     * Fetch a phpredis instance
     *
     * @return \Redis
     */
    public function getRedis ()
    {
        if (null === $this->redis) {
            $redis = CacheRedis::getRedisInstance($this->server);
            if (! empty($this->server['db']))
                $redis->select($this->server['db']);
            if (! empty($this->server['auth']))
                $redis->auth($this->server['auth']);
            $this->redis = $redis;
        }
        return $this->redis;
    }

    /**
     * Adds an element to the queue.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function enqueue ($value)
    {
        return $this->getRedis()->lPush($this->queueKey, $value);
    }

    /**
     * Dequeues a node from the queue.
     *
     * @return mixed
     */
    public function dequeue ()
    {
        return $this->getRedis()->rPop($this->queueKey);
    }
}
