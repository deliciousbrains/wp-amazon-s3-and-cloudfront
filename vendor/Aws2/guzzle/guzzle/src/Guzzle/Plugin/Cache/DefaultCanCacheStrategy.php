<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
/**
 * Default strategy used to determine of an HTTP request can be cached
 */
class DefaultCanCacheStrategy implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\CanCacheStrategyInterface
{
    public function canCacheRequest(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request)
    {
        // Only GET and HEAD requests can be cached
        if ($request->getMethod() != \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface::GET && $request->getMethod() != \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface::HEAD) {
            return false;
        }
        // Never cache requests when using no-store
        if ($request->hasHeader('Cache-Control') && $request->getHeader('Cache-Control')->hasDirective('no-store')) {
            return false;
        }
        return true;
    }
    public function canCacheResponse(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        return $response->isSuccessful() && $response->canCache();
    }
}
