<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command;

/**
 * Interface used to accept a completed OperationCommand and parse the result into a specific response type
 */
interface ResponseClassInterface
{
    /**
     * Create a response model object from a completed command
     *
     * @param OperationCommand $command That serialized the request
     *
     * @return self
     */
    public static function fromCommand(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\OperationCommand $command);
}
