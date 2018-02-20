<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
/**
 * Strategy used to determine if a request can be cached
 */
interface CanCacheStrategyInterface
{
    /**
     * Determine if a request can be cached
     *
     * @param RequestInterface $request Request to determine
     *
     * @return bool
     */
    public function canCacheRequest(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request);
    /**
     * Determine if a response can be cached
     *
     * @param Response $response Response to determine
     *
     * @return bool
     */
    public function canCacheResponse(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response);
}
