<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter;
/**
 * {@inheritdoc}
 * @codeCoverageIgnore
 */
abstract class AbstractResponseVisitor implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response\ResponseVisitorInterface
{
    public function before(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, array &$result)
    {
    }
    public function after(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
    }
    public function visit(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $param, &$value, $context = null)
    {
    }
}
