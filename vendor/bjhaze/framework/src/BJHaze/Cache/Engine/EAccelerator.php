<?php
/**
 *
 * EAccelerator Cache.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

class EAccelerator implements CacheInterface
{

    protected $prefix;

    /**
     * Cache Constructor
     *
     * @throws Exception if eAccelerator extension is not loaded or is disabled.
     */
    public function __construct ($prefix)
    {
        if (! function_exists('eaccelerator_get'))
            throw new \RuntimeException(
                    'BJHaze\Cache\EAccelerator requires PHP eAccelerator extension to be loaded, enabled or compiled with the "--with-eaccelerator-shared-memory" option.');
        $this->prefix = $prefix;
    }

    public function get ($key)
    {
        $result = eaccelerator_get($this->prefix . $key);
        return $result !== NULL ? $result : false;
    }

    public function mget (array $keys)
    {
        $results = array();
        foreach ($keys as $key)
            $results[$key] = $this->get($this->prefix . $key);
        return $results;
    }

    public function set ($key, $value, $ttl)
    {
        return eaccelerator_put($this->prefix . $key, $value, $ttl);
    }

    public function mset (array $items, $ttl = 0)
    {
        $rt = true;
        foreach ($items as $key => $value)
            $rt &= eaccelerator_put($this->prefix . $key, $value, $ttl);
        return $rt;
    }

    public function add ($key, $value, $ttl)
    {
        return (NULL === eaccelerator_get($this->prefix . $key)) ? $this->setValue($this->prefix . $key, $value, $ttl) : false;
    }

    public function delete ($key)
    {
        return eaccelerator_rm($this->prefix . $key);
    }

    public function flush ()
    {
        // only remove expired content from cache
        eaccelerator_gc();
        /**
         * // now, remove leftover cache-keys
         * $keys = eaccelerator_list_keys();
         * foreach ($keys as $key)
         * $this->delete(substr($key['name'], 1));*
         */
        return true;
    }

    public function decrement ($key, $step = 1)
    {
        throw new \LogicException('EAccelerator don\'t support dec method');
    }

    public function increment ($key, $step = 1)
    {
        throw new \LogicException('EAccelerator don\'t support inc method');
    }
}
