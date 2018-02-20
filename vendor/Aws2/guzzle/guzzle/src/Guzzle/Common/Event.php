<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common;

use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\Event as SymfonyEvent;
/**
 * Default event for Guzzle notifications
 */
class Event extends \DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\Event implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\ToArrayInterface, \ArrayAccess, \IteratorAggregate
{
    /** @var array */
    private $context;
    /**
     * @param array $context Contextual information
     */
    public function __construct(array $context = array())
    {
        $this->context = $context;
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->context);
    }
    public function offsetGet($offset)
    {
        return isset($this->context[$offset]) ? $this->context[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        $this->context[$offset] = $value;
    }
    public function offsetExists($offset)
    {
        return isset($this->context[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->context[$offset]);
    }
    public function toArray()
    {
        return $this->context;
    }
}
