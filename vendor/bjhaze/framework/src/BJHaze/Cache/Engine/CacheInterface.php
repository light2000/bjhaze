<?php
/**
 *
 * Cache Interface.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Cache\Engine;

interface CacheInterface
{

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key            
     * @return mixed
     */
    public function get($key);

    /**
     * Retrieves multiple values from cache with the specified keys.
     *
     * @param array $keys            
     * @return array
     */
    public function mget(array $keys);

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key            
     * @param mixed $value            
     * @param int $ttl            
     * @return void
     */
    public function set($key, $value, $ttl);

    /**
     * Stores values by key - value pairs.
     * 
     * @param array $items            
     * @param int $ttl
     *            the number of seconds in which the cached value will
     *            expire. 0 means never expire.
     * @return void
     */
    public function mset(array $items, $ttl = 0);

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function decrement($key, $value = 1);

    /**
     * Remove an item from the cache.
     *
     * @param string $key            
     * @return void
     */
    public function delete($key);

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush();
}
