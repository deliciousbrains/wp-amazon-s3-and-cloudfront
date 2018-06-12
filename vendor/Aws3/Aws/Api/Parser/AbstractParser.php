<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\ResultInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * @internal
 */
abstract class AbstractParser
{
    /** @var \Aws\Api\Service Representation of the service API*/
    protected $api;
    /**
     * @param Service $api Service description.
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service $api)
    {
        $this->api = $api;
    }
    /**
     * @param CommandInterface  $command  Command that was executed.
     * @param ResponseInterface $response Response that was received.
     *
     * @return ResultInterface
     */
    public abstract function __invoke(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response);
}
