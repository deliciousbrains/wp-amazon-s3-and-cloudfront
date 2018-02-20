<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
/**
 * Never performs cache revalidation and just assumes the request is invalid
 */
class DenyRevalidation extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\DefaultRevalidation
{
    public function __construct()
    {
    }
    public function revalidate(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        return false;
    }
}
