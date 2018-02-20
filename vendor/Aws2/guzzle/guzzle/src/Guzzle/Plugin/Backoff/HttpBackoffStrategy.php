<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException;
/**
 * Strategy used to retry HTTP requests based on the response code.
 *
 * Retries 500 and 503 error by default.
 */
class HttpBackoffStrategy extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractErrorCodeBackoffStrategy
{
    /** @var array Default cURL errors to retry */
    protected static $defaultErrorCodes = array(500, 503);
    protected function getDelay($retries, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response = null, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException $e = null)
    {
        if ($response) {
            //Short circuit the rest of the checks if it was successful
            if ($response->isSuccessful()) {
                return false;
            } else {
                return isset($this->errorCodes[$response->getStatusCode()]) ? true : null;
            }
        }
    }
}
