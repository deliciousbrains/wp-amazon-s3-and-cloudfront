<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache\CacheAdapterInterface;
/**
 * Decorator that adds caching to a service description loader
 */
class CachingConfigLoader implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\ConfigLoaderInterface
{
    /** @var ConfigLoaderInterface */
    protected $loader;
    /** @var CacheAdapterInterface */
    protected $cache;
    /**
     * @param ConfigLoaderInterface $loader Loader used to load the config when there is a cache miss
     * @param CacheAdapterInterface $cache  Object used to cache the loaded result
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\ConfigLoaderInterface $loader, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Cache\CacheAdapterInterface $cache)
    {
        $this->loader = $loader;
        $this->cache = $cache;
    }
    public function load($config, array $options = array())
    {
        if (!is_string($config)) {
            $key = false;
        } else {
            $key = 'loader_' . crc32($config);
            if ($result = $this->cache->fetch($key)) {
                return $result;
            }
        }
        $result = $this->loader->load($config, $options);
        if ($key) {
            $this->cache->save($key, $result);
        }
        return $result;
    }
}
