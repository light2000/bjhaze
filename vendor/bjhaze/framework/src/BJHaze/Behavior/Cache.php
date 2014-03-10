<?php
/**
 *
 * Cache behavior.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Behavior;
use ReflectionMethod, Closure;
use BJHaze\Foundation\Component;

class Cache extends Component implements BehaviorInterface
{

    /**
     * Cache key
     *
     * @var string
     */
    protected $key;

    /**
     * Cache live time
     *
     * @var int
     */
    protected $second;

    /**
     * Cache engine
     *
     * @var string
     */
    protected $engine;

    /**
     * Set The cache key
     *
     * @param string $key
     * @return void
     */
    public function setKey ($key)
    {
        $this->key = sha1($key);
    }

    /**
     * Set cache live time
     *
     * @param int $second
     */
    public function setSecond ($second)
    {
        $this->second = $second;
    }

    /**
     * Set cache engine
     *
     * @param string $second
     */
    public function setEngine ($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Fetch a chain return from cache.
     * if not hit, this function will append a chain_set_cache action.
     *
     * @param mixed $method
     * @param array $parameters
     * @param array $chain
     * @param mixed $return
     * @throws LogicException
     */
    public function handle (&$action, array &$parameters, array &$before, array &$after, &$result)
    {
        if (null !== $result)
            throw new \LogicException(
                    'You can not put cache behavior after you have found a return value');
        if (null === $this->key || null === $this->second)
            throw new \LogicException(
                    'You can not use cache behavior without set cache key and live second');
        if (null == $this['cache']) // no cache component found
            return;
        
        if (false !== ($data = $this->cache->getEngine($this->engine)->get($this->key))) {
            $action = null; // cache hit, end the behavior chain.
            $before = $after = array();
            $result = $data;
        } else {
            $after[] = function  (&$action, array &$parameters, array &$before, array &$after, 
                    &$result)
            {
                $this->app->finishRun(
                        function  () use( $result)
                        {
                            if (null === $result)
                                $result = $this->response->getContent();
                            if (null !== $result)
                                $ret = $this->cache->getEngine($this->engine)
                                    ->set($this->key, $result, $this->second);
                        });
            };
        }
    }
}