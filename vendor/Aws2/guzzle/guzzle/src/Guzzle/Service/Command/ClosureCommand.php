<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\UnexpectedValueException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
/**
 * A ClosureCommand is a command that allows dynamic commands to be created at runtime using a closure to prepare the
 * request. A closure key and \Closure value must be passed to the command in the constructor. The closure must
 * accept the command object as an argument.
 */
class ClosureCommand extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\AbstractCommand
{
    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if a closure was not passed
     */
    protected function init()
    {
        if (!$this['closure']) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException('A closure must be passed in the parameters array');
        }
    }
    /**
     * {@inheritdoc}
     * @throws UnexpectedValueException If the closure does not return a request
     */
    protected function build()
    {
        $closure = $this['closure'];
        /** @var $closure \Closure */
        $this->request = $closure($this, $this->operation);
        if (!$this->request || !$this->request instanceof RequestInterface) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\UnexpectedValueException('Closure command did not return a RequestInterface object');
        }
    }
}
