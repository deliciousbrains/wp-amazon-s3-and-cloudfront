<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws;

use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Cache\CacheItemPoolInterface;
class PsrCacheAdapter implements \DeliciousBrains\WP_Offload_S3\Aws3\Aws\CacheInterface
{
    /** @var CacheItemPoolInterface */
    private $pool;
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Psr\Cache\CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }
    public function get($key)
    {
        $item = $this->pool->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }
    public function set($key, $value, $ttl = 0)
    {
        $item = $this->pool->getItem($key);
        $item->set($value);
        if ($ttl > 0) {
            $item->expiresAfter($ttl);
        }
        $this->pool->save($item);
    }
    public function remove($key)
    {
        $this->pool->deleteItem($key);
    }
}
