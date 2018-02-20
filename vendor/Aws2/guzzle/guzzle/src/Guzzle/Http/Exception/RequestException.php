<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\RuntimeException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
/**
 * Http request exception
 */
class RequestException extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\RuntimeException implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException
{
    /** @var RequestInterface */
    protected $request;
    /**
     * Set the request that caused the exception
     *
     * @param RequestInterface $request Request to set
     *
     * @return RequestException
     */
    public function setRequest(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
    /**
     * Get the request that caused the exception
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
