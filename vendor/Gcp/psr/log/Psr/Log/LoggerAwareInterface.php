<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Log\LoggerInterface $logger);
}
