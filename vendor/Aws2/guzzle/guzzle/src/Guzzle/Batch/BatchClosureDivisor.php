<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException;
/**
 * Divides batches using a callable
 */
class BatchClosureDivisor implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchDivisorInterface
{
    /** @var callable Method used to divide the batches */
    protected $callable;
    /** @var mixed $context Context passed to the callable */
    protected $context;
    /**
     * @param callable $callable Method used to divide the batches. The method must accept an \SplQueue and return an
     *                           array of arrays containing the divided items.
     * @param mixed    $context  Optional context to pass to the batch divisor
     *
     * @throws InvalidArgumentException if the callable is not callable
     */
    public function __construct($callable, $context = null)
    {
        if (!is_callable($callable)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException('Must pass a callable');
        }
        $this->callable = $callable;
        $this->context = $context;
    }
    public function createBatches(\SplQueue $queue)
    {
        return call_user_func($this->callable, $queue, $this->context);
    }
}
