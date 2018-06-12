<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Exception;

use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\RequestInterface;
/**
 * Exception thrown when a connection cannot be established.
 *
 * Note that no response is present for a ConnectException
 */
class ConnectException extends \DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Exception\RequestException
{
    public function __construct($message, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\RequestInterface $request, \Exception $previous = null, array $handlerContext = [])
    {
        parent::__construct($message, $request, null, $previous, $handlerContext);
    }
    /**
     * @return null
     */
    public function getResponse()
    {
        return null;
    }
    /**
     * @return bool
     */
    public function hasResponse()
    {
        return false;
    }
}
