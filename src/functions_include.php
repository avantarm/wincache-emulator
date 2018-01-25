<?php

use Avantarm\WincacheEmulator;

if (!\function_exists('wincache_ucache_exists')) {
    /**
     * Checks if a variable exists in the user cache.
     * @param  string $key
     * @return bool
     * @see wincache_ucache_exists()
     */
    function wincache_ucache_exists($key)
    {
        return WincacheEmulator::exists($key);
    }

    /**
     * Gets a variable stored in the user cache.
     * @param mixed $key
     * @param bool $success
     * @return mixed
     * @see wincache_ucache_get()
     */
    function wincache_ucache_get($key, &$success = null)
    {
        return WincacheEmulator::get($key, $success);
    }

    /**
     * Adds a variable in user cache and overwrites a variable if it already exists in the cache.
     * @param  mixed $key
     * @param  mixed $value
     * @param  int $ttl
     * @return mixed
     * @see wincache_ucache_set()
     */
    function wincache_ucache_set($key, $value, $ttl = 0)
    {
        return WincacheEmulator::set($key, $value, $ttl);
    }

    /**
     * Adds a variable in user cache only if variable does not already exist in the cache.
     * @param  mixed $key
     * @param  mixed $value
     * @param  int $ttl
     * @return mixed
     * @see wincache_ucache_add()
     */
    function wincache_ucache_add($key, $value, $ttl = 0)
    {
        return WincacheEmulator::add($key, $value, $ttl);
    }

    /**
     * Increments the value associated with the key.
     * @param  mixed $key
     * @return mixed
     * @see wincache_ucache_delete()
     */
    function wincache_ucache_delete($key)
    {
        return WincacheEmulator::delete($key);
    }

    /**
     * @param  string $key
     * @param  int $inc_by
     * @param  bool $success
     * @return mixed
     * @see wincache_ucache_inc()
     */
    function wincache_ucache_inc($key, $inc_by = 1, &$success = null)
    {
        return WincacheEmulator::inc($key, $inc_by, $success);
    }

    /**
     * Decrements the value associated with the key.
     * @param  string $key
     * @param  int $dec_by
     * @param  bool $success
     * @return mixed
     * @see wincache_ucache_inc()
     */
    function wincache_ucache_dec($key, $dec_by = 1, &$success = null)
    {
        return WincacheEmulator::dec($key, $dec_by, $success);
    }

    /**
     * Deletes entire content of the user cache.
     * @return  bool
     * @see wincache_ucache_clear()
     */
    function wincache_ucache_clear()
    {
        return WincacheEmulator::clear();
    }

    /**
     * Retrieves information about data stored in the user cache.
     * @param  bool $summaryonly
     * @param  string $key
     * @return array
     * @see wincache_ucache_info()
     */
    function wincache_ucache_info($summaryonly = false, $key = null)
    {
        return WincacheEmulator::info($summaryonly, $key);
    }

    /**
     * Retrieves information about user cache memory usage
     * @return array|false Array of meta data about user cache memory usage or FALSE on failure.
     */
    function wincache_ucache_meminfo()
    {
        return WincacheEmulator::meminfo();
    }
}
