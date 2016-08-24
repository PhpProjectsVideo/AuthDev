<?php

namespace PhpProjects\AuthDev\Memcache;

/**
 * Handles operations with memcache.
 *
 * Provides direct access to the memcached object as well as a layer overtop supporting namespaces.
 *
 * Namespaces increase the number of memcache calls but allow us to invalidate entire keys of data in one sweep. This
 * allows us improved cacheability as we can focus flushes to a segment of keys instead of flushing the entire server.
 */
class MemcacheService
{
    /**
     * @var array
     */
    private static $servers = [];

    /**
     * @var MemcacheService
     */
    private static $instance;

    /**
     * @var string
     */
    private static $prefix = '';

    /**
     * @var \Memcache
     */
    private $memcached;

    /**
     * MemcacheService constructor.
     * @param \Memcache $memcached
     */
    public function __construct(\Memcache $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Stores $value in $key in $namespace for $ttl seconds.
     * @param string $namespace
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function nsSet(string $namespace, string $key, $value, int $ttl)
    {
        $nsValue = $this->memcached->get(self::$prefix . '_ns_' . $namespace) ?: 0;

        $this->memcached->set(self::$prefix . '_ns_' . $namespace . '_' . $nsValue . '_' . $key, $value, 0, $ttl);
    }

    /**
     * Retrieves the $key from the given $namespace
     * @param string $namespace
     * @param string $key
     * @return mixed
     */
    public function nsGet(string $namespace, string $key)
    {
        $nsValue = $this->memcached->get(self::$prefix . '_ns_' . $namespace) ?: 0;

        return $this->memcached->get(self::$prefix . '_ns_' . $namespace . '_' . $nsValue . '_' . $key);
    }

    /**
     * Flushes data from all keys in a given namespace.
     *
     * Keys are not actually removed, the namespace that is used to build the keys is incremented instead. The old
     * values will eventually fall off due to LRU.
     *
     * @param string $namespace
     */
    public function nsFlush(string $namespace)
    {
        $key = self::$prefix . '_ns_' . $namespace;
        $value = $this->memcached->increment($key);
        if (!$value)
        {
            $this->memcached->set($key, 1);
        }
    }

    /**
     * Sets the servers for memcache. Must be called before getInstance().
     *
     * @param array $servers
     */
    public static function setServers(array $servers)
    {
        self::$servers = $servers;
    }

    /**
     * Flushes all data from all namespaces and keys
     */
    public function fullFlush()
    {
        $this->memcached->flush();
    }

    /**
     * Returns a shared instance of memcached.
     *
     * @return MemcacheService
     */
    public static function getInstance() : MemcacheService
    {
        if (empty(self::$instance))
        {
            $memcached = new \Memcache();

            foreach (self::$servers as $server)
            {
                $memcached->addserver($server['host'], $server['port']);
            }
            self::$instance = new self($memcached);
        }

        return self::$instance;
    }

    /**
     * Sets a global prefix for all namespaces.
     * 
     * @param string $prefix
     */
    public static function setNsPrefix(string $prefix)
    {
        self::$prefix = $prefix;
    }
}