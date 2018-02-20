<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\InflectorInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\Inflector;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
/**
 * Factory for creating {@see ResourceIteratorInterface} objects using a convention of storing iterator classes under a
 * root namespace using the name of a {@see CommandInterface} object as a convention for determining the name of an
 * iterator class. The command name is converted to CamelCase and Iterator is appended (e.g. abc_foo => AbcFoo).
 */
class ResourceIteratorClassFactory extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\AbstractResourceIteratorFactory
{
    /** @var array List of namespaces used to look for classes */
    protected $namespaces;
    /** @var InflectorInterface Inflector used to determine class names */
    protected $inflector;
    /**
     * @param string|array       $namespaces List of namespaces for iterator objects
     * @param InflectorInterface $inflector  Inflector used to resolve class names
     */
    public function __construct($namespaces = array(), \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\InflectorInterface $inflector = null)
    {
        $this->namespaces = (array) $namespaces;
        $this->inflector = $inflector ?: \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Inflection\Inflector::getDefault();
    }
    /**
     * Registers a namespace to check for Iterators
     *
     * @param string $namespace Namespace which contains Iterator classes
     *
     * @return self
     */
    public function registerNamespace($namespace)
    {
        array_unshift($this->namespaces, $namespace);
        return $this;
    }
    protected function getClassName(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        $iteratorName = $this->inflector->camel($command->getName()) . 'Iterator';
        // Determine the name of the class to load
        foreach ($this->namespaces as $namespace) {
            $potentialClassName = $namespace . '\\' . $iteratorName;
            if (class_exists($potentialClassName)) {
                return $potentialClassName;
            }
        }
        return false;
    }
}
