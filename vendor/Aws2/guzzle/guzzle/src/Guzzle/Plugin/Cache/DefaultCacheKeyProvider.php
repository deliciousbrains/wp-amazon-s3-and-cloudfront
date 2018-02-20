<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version::warn('DeliciousBrains\\WP_Offload_S3\\Aws2\\Guzzle\\Plugin\\Cache\\DefaultCacheKeyProvider is no longer used');
/**
 * @deprecated This class is no longer used
 * @codeCoverageIgnore
 */
class DefaultCacheKeyProvider implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\CacheKeyProviderInterface
{
    public function getCacheKey(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request)
    {
        // See if the key has already been calculated
        $key = $request->getParams()->get(self::CACHE_KEY);
        if (!$key) {
            $cloned = clone $request;
            $cloned->removeHeader('Cache-Control');
            // Check to see how and if the key should be filtered
            foreach (explode(';', $request->getParams()->get(self::CACHE_KEY_FILTER)) as $part) {
                $pieces = array_map('trim', explode('=', $part));
                if (isset($pieces[1])) {
                    foreach (array_map('trim', explode(',', $pieces[1])) as $remove) {
                        if ($pieces[0] == 'header') {
                            $cloned->removeHeader($remove);
                        } elseif ($pieces[0] == 'query') {
                            $cloned->getQuery()->remove($remove);
                        }
                    }
                }
            }
            $raw = (string) $cloned;
            $key = 'GZ' . md5($raw);
            $request->getParams()->set(self::CACHE_KEY, $key)->set(self::CACHE_KEY_RAW, $raw);
        }
        return $key;
    }
}
