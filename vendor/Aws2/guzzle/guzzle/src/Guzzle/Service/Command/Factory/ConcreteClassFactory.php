<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\Factory;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\InflectorInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\Inflector;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\ClientInterface;
/**
 * Command factory used to create commands referencing concrete command classes
 */
class ConcreteClassFactory implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\Factory\FactoryInterface
{
    /** @var ClientInterface */
    protected $client;
    /** @var InflectorInterface */
    protected $inflector;
    /**
     * @param ClientInterface    $client    Client that owns the commands
     * @param InflectorInterface $inflector Inflector used to resolve class names
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\ClientInterface $client, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\InflectorInterface $inflector = null)
    {
        $this->client = $client;
        $this->inflector = $inflector ?: \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\Inflector::getDefault();
    }
    public function factory($name, array $args = array())
    {
        // Determine the class to instantiate based on the namespace of the current client and the default directory
        $prefix = $this->client->getConfig('command.prefix');
        if (!$prefix) {
            // The prefix can be specified in a factory method and is cached
            $prefix = implode('\\', array_slice(explode('\\', get_class($this->client)), 0, -1)) . '\\Command\\';
            $this->client->getConfig()->set('command.prefix', $prefix);
        }
        $class = $prefix . str_replace(' ', '\\', ucwords(str_replace('.', ' ', $this->inflector->camel($name))));
        // Create the concrete command if it exists
        if (class_exists($class)) {
            return new $class($args);
        }
    }
}
