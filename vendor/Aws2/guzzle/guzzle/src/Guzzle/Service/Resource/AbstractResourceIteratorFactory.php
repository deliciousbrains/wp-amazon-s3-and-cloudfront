<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
/**
 * Abstract resource iterator factory implementation
 */
abstract class AbstractResourceIteratorFactory implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorFactoryInterface
{
    public function build(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, array $options = array())
    {
        if (!$this->canBuild($command)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException('Iterator was not found for ' . $command->getName());
        }
        $className = $this->getClassName($command);
        return new $className($command, $options);
    }
    public function canBuild(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        return (bool) $this->getClassName($command);
    }
    /**
     * Get the name of the class to instantiate for the command
     *
     * @param CommandInterface $command Command that is associated with the iterator
     *
     * @return string
     */
    protected abstract function getClassName(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command);
}
