<?php
/**
 *
 * Apc Cache.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

class Apc implements CacheInterface
{

    protected $prefix;

    /**
     * Cache Constructor
     *
     * @throws Exception if APC cache extension is not loaded or is disabled.
     */
    public function __construct ($prefix)
    {
        if (! extension_loaded('apc'))
            throw new \RuntimeException('Requires PHP apc extension to be loaded.');
        $this->prefix = $prefix;
    }

    public function set ($key, $value, $ttl = 0)
    {
        return apc_store($this->prefix . $key, $value, $ttl);
    }

    public function mset (array $items, $ttl = 0)
    {
        $rt = true;
        foreach ($items as $key => $value)
            $rt &= apc_store($this->prefix . $key, $value, $ttl);
        return $rt;
    }

    public function get ($key)
    {
        return apc_fetch($this->prefix . $key);
    }

    public function mget (array $keys)
    {
        return apc_fetch(array_map(function($key){return $this->prefix . $key;}, $keys));
    }

    public function delete ($key)
    {
        return apc_delete($this->prefix . $key);
    }

    public function flush ()
    {
        return apc_clear_cache('user');
    }

    public function increment ($key, $step = 1)
    {
        apc_inc($this->prefix . $key, $step);
    }

    public function decrement ($key, $step = 1)
    {
        return apc_dec($this->prefix . $key, $step);
    }

    /**
     * Set integer value when old value matched
     *
     * @param string $key
     * @param int $new
     * @param int $old
     */
    public function cas ($key, $old, $new)
    {
        return apc_cas($this->prefix . $key, $old, $new);
    }

    public function add ($key, $val, $ttl = 0)
    {
        return apc_add($this->prefix . $key, $val, $ttl);
    }
}