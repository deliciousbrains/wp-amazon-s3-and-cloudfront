<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache;

/**
 * Abstract cache adapter
 */
abstract class AbstractCacheAdapter implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache\CacheAdapterInterface
{
    protected $cache;
    /**
     * Get the object owned by the adapter
     *
     * @return mixed
     */
    public function getCacheObject()
    {
        return $this->cache;
    }
}
