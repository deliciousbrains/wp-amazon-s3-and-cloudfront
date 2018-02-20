<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Doctrine\Common\Cache\Cache;
/**
 * Doctrine 2 cache adapter
 *
 * @link http://www.doctrine-project.org/
 */
class DoctrineCacheAdapter extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache\AbstractCacheAdapter
{
    /**
     * @param Cache $cache Doctrine cache object
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Doctrine\Common\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }
    public function contains($id, array $options = null)
    {
        return $this->cache->contains($id);
    }
    public function delete($id, array $options = null)
    {
        return $this->cache->delete($id);
    }
    public function fetch($id, array $options = null)
    {
        return $this->cache->fetch($id);
    }
    public function save($id, $data, $lifeTime = false, array $options = null)
    {
        return $this->cache->save($id, $data, $lifeTime !== false ? $lifeTime : 0);
    }
}
