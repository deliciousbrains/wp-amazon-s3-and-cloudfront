<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\Factory;

/**
 * Command factory used when explicitly mapping strings to command classes
 */
class MapFactory implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\Factory\FactoryInterface
{
    /** @var array Associative array mapping command names to classes */
    protected $map;
    /** @param array $map Associative array mapping command names to classes */
    public function __construct(array $map)
    {
        $this->map = $map;
    }
    public function factory($name, array $args = array())
    {
        if (isset($this->map[$name])) {
            $class = $this->map[$name];
            return new $class($args);
        }
    }
}
