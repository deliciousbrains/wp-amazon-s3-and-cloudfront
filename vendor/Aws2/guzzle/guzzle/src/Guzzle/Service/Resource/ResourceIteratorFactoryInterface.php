<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
/**
 * Factory for creating {@see ResourceIteratorInterface} objects
 */
interface ResourceIteratorFactoryInterface
{
    /**
     * Create a resource iterator
     *
     * @param CommandInterface $command Command to create an iterator for
     * @param array                 $options Iterator options that are exposed as data.
     *
     * @return ResourceIteratorInterface
     */
    public function build(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, array $options = array());
    /**
     * Check if the factory can create an iterator
     *
     * @param CommandInterface $command Command to create an iterator for
     *
     * @return bool
     */
    public function canBuild(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command);
}
