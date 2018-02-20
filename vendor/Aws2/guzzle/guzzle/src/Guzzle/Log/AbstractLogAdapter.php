<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

/**
 * Adapter class that allows Guzzle to log data using various logging implementations
 */
abstract class AbstractLogAdapter implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\LogAdapterInterface
{
    protected $log;
    public function getLogObject()
    {
        return $this->log;
    }
}
