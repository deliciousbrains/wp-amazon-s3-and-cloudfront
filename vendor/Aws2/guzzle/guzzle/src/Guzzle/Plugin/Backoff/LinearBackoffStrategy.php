<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException;
/**
 * Implements a linear backoff retry strategy.
 *
 * Warning: If no decision making strategies precede this strategy in the the chain, then all requests will be retried
 */
class LinearBackoffStrategy extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy
{
    /** @var int Amount of time to progress each delay */
    protected $step;
    /**
     * @param int $step Amount of time to increase the delay each additional backoff
     */
    public function __construct($step = 1)
    {
        $this->step = $step;
    }
    public function makesDecision()
    {
        return false;
    }
    protected function getDelay($retries, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response = null, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException $e = null)
    {
        return $retries * $this->step;
    }
}
